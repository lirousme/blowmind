<?php

declare(strict_types=1);

namespace App\Core;

use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;

final class Database
{
    private static ?Client $client = null;

    public static function client(): Client
    {
        if (self::$client === null) {
            $config = require __DIR__ . '/../../config/database.php';

            self::$client = ClientBuilder::create()
                ->withDriver('default', $config['uri'])
                ->build();
        }

        return self::$client;
    }
}
