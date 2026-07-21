<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SetupController extends Controller
{
    public function index()
    {
        return view('setup.index');
    }

    /**
     * AJAX: verify the supplied MySQL credentials actually connect, without
     * persisting anything yet.
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $result = $this->attemptConnection($request->all());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function store(Request $request)
    {
        $request->validate([
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $connectionTest = $this->attemptConnection($request->all());

        if (! $connectionTest['success']) {
            return back()->withInput()->withErrors(['db_host' => $connectionTest['message']]);
        }

        try {
            $this->writeEnvDatabaseCredentials($request->all());

            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);

            $admin = User::create([
                'name' => $request->input('admin_name'),
                'email' => $request->input('admin_email'),
                'password' => Hash::make($request->input('admin_password')),
                'role' => 'admin',
            ]);

            Artisan::call('db:seed', ['--class' => 'CopperStandardSeeder', '--force' => true]);

            if ($request->boolean('load_demo_data')) {
                Artisan::call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true]);
            }

            Activity::log('Application installed and first admin account created', 'bi-rocket-takeoff-fill', 'success');

            @file_put_contents(storage_path('app/installed.lock'), now()->toDateTimeString());

            Auth::login($admin);

            return redirect()->route('dashboard')->with('success', 'Setup complete. Welcome to Vivek Jain CRM!');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['db_host' => 'Setup failed: '.$e->getMessage()]);
        }
    }

    protected function attemptConnection(array $data): array
    {
        config([
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => $data['db_port'],
            'database.connections.mysql.database' => $data['db_database'],
            'database.connections.mysql.username' => $data['db_username'],
            'database.connections.mysql.password' => $data['db_password'] ?? '',
        ]);

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');

        try {
            DB::connection('mysql')->getPdo();

            return ['success' => true, 'message' => 'Connection successful.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Could not connect: '.$e->getMessage()];
        }
    }

    protected function writeEnvDatabaseCredentials(array $data): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';

        $replacements = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ];

        foreach ($replacements as $key => $value) {
            $escaped = str_contains((string) $value, ' ') || $value === '' ? '"'.$value.'"' : $value;
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $key.'='.$escaped, $contents);
            } else {
                $contents .= PHP_EOL.$key.'='.$escaped;
            }
        }

        file_put_contents($path, $contents);
    }
}
