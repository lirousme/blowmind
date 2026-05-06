<?php

declare(strict_types=1);

namespace App\Core;

use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;

final class Database
{
    private static ?Client $client = null;
    private static bool $indexesEnsured = false;

    public static function client(): Client
    {
        if (self::$client === null) {
            $config = require __DIR__ . '/../../config/database.php';

            self::$client = ClientBuilder::create()
                ->withDriver('default', $config['uri'])
                ->build();
        }

        self::ensureIndexes();

        return self::$client;
    }

    private static function ensureIndexes(): void
    {
        if (self::$indexesEnsured) {
            return;
        }

        self::$client?->run(
            'CREATE INDEX node_nome_lookup_index IF NOT EXISTS FOR (n:Node) ON (n.nome)'
        );

        self::$indexesEnsured = true;
    }
}
