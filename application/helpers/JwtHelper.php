<?php

namespace App\Helpers;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtHelper
{
    private static $key;
    private static $algo = 'HS256'; // Define the algorithm to use

    // Initialize key from the .env file
    private static function init()
    {
        echo "Initializing key\n";
        if (!self::$key) {
            self::$key = 'af0e4b7ca1c8e091fb9a781c9a2b5f07340ea4d88f96a3b5b1b9927710460f1a'; // Hardcoded for now
        }
        echo "Key after init: " . self::$key . "\n";
    }
    public static function encode($payload)
    {
        echo "Inside encode method\n";
        self::init(); // Ensure the key is initialized
        echo "Key initialized: " . self::$key . "\n";
        try {
            return JWT::encode($payload, self::$key, self::$algo); // Generate token
        } catch (\Exception $e) {
            echo 'Error in encode: ' . $e->getMessage(); // Catch and display errors
            exit; // Exit after printing error
        }
    }

    public static function decode($token)
    {
        self::init();
        // Pass the key and algorithm in the proper format
        return JWT::decode($token, new Key(self::$key, self::$algo));
    }
}
