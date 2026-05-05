<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GraphModel
{
    public function createNode(string $label, array $properties): void
    {
        $query = sprintf('CREATE (n:%s $props)', $label);
        Database::client()->run($query, ['props' => $properties]);
    }

    public function createRelationship(
        string $fromLabel,
        string $fromKey,
        string $fromValue,
        string $toLabel,
        string $toKey,
        string $toValue,
        string $relationshipType
    ): void {
        $query = sprintf(
            'MATCH (a:%s {%s: $fromValue}), (b:%s {%s: $toValue}) CREATE (a)-[:%s]->(b)',
            $fromLabel,
            $fromKey,
            $toLabel,
            $toKey,
            $relationshipType
        );

        Database::client()->run($query, [
            'fromValue' => $fromValue,
            'toValue' => $toValue,
        ]);
    }
}
