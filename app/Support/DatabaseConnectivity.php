<?php

namespace App\Support;

use Throwable;

class DatabaseConnectivity
{
    /**
     * Substrings that show up in a QueryException's message when the
     * database server itself couldn't be reached — as opposed to a real
     * bug (bad SQL, constraint violation, wrong credentials), which should
     * still surface normally so it gets noticed and fixed.
     */
    protected const CONNECTIVITY_INDICATORS = [
        'sqlstate[hy000] [2002]', // Connection refused
        'sqlstate[hy000] [2003]', // Can't connect to MySQL server
        'sqlstate[hy000] [2005]', // Unknown MySQL server host
        'sqlstate[hy000] [2006]', // MySQL server has gone away
        'connection refused',
        'connection timed out',
        'getaddrinfo',
        'no route to host',
        'network is unreachable',
        'name or service not known',
        'temporary failure in name resolution',
    ];

    public static function isConnectivityFailure(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        foreach (self::CONNECTIVITY_INDICATORS as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
