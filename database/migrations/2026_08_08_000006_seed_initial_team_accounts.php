<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-time creation of the initial team logins requested by the client.
     * Passwords are never stored in the repo — only their bcrypt hashes,
     * generated offline. updateOrCreate-by-email makes this safe to leave
     * in migration history without ever re-running (Laravel tracks it as
     * already applied) and without clobbering a password the user later
     * changes themselves via Settings.
     */
    protected array $accounts = [
        ['name' => 'Admin', 'email' => 'admin@onealldigi.com', 'role' => 'admin', 'hash' => '$2y$12$ZjU1JhgS74olzj4c6eb.Q.AGbjT2gHGHr3cW0W/dh1zQEIwjRrZMO'],
        ['name' => 'User One', 'email' => 'user1@onealldigi.com', 'role' => 'user', 'hash' => '$2y$12$4BYEML.feF/Suunt01L2YulQLWWZCvBVYJi1jV/unf/zkjNmxCocu'],
        ['name' => 'User Two', 'email' => 'user2@onealldigi.com', 'role' => 'user', 'hash' => '$2y$12$4U.zqxoqRoCbloIq16Zpju8RrGvtLHU.UlfIB3vc0lSYlBc3kMmtS'],
        ['name' => 'User Three', 'email' => 'user3@onealldigi.com', 'role' => 'user', 'hash' => '$2y$12$vU5Fy.Or9aHXmBvZc0nmb.DIuT6LqQT0va3DMzZ9PK7cQZ1buH.fa'],
        ['name' => 'User Four', 'email' => 'user4@onealldigi.com', 'role' => 'user', 'hash' => '$2y$12$WhCR3s5QmUksc4jHmIt5b.RiVTdv841T1kwzhWStFfxW/UwJHs4Bu'],
        ['name' => 'User Five', 'email' => 'user5@onealldigi.com', 'role' => 'user', 'hash' => '$2y$12$mjRvqX6OuFUYzgt5m965duqN.ffyHkY.l5EiMKw0/UgcvhP/V9mSO'],
    ];

    public function up(): void
    {
        foreach ($this->accounts as $account) {
            if (DB::table('users')->where('email', $account['email'])->exists()) {
                continue;
            }

            DB::table('users')->insert([
                'name' => $account['name'],
                'email' => $account['email'],
                'role' => $account['role'],
                'password' => $account['hash'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->whereIn('email', array_column($this->accounts, 'email'))->delete();
    }
};
