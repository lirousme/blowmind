<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GraphModel
{
    public function findNamesByPrefix(string $query, int $limit = 8): array
    {
        if ($query === '') {
            return [];
        }

        $result = Database::client()->run(
            'MATCH (n) WHERE exists(n.nome) AND toLower(n.nome) STARTS WITH toLower($query) RETURN DISTINCT n.nome AS nome ORDER BY nome LIMIT $limit',
            ['query' => $query, 'limit' => $limit]
        );

        return array_values(array_filter(array_map(
            static fn ($record): string => (string) $record->get('nome'),
            iterator_to_array($result)
        )));
    }

    public function getRelationshipTypes(): array
    {
        $result = Database::client()->run('CALL db.relationshipTypes() YIELD relationshipType RETURN relationshipType ORDER BY relationshipType');

        return array_values(array_filter(array_map(
            static fn ($record): string => (string) $record->get('relationshipType'),
            iterator_to_array($result)
        )));
    }

    public function createNode(string $label, array $properties): void
    {
        $query = sprintf('CREATE (n:%s $props)', $label);
        Database::client()->run($query, ['props' => $properties]);
    }

    public function createRelationshipByName(
        string $fromName,
        string $toName,
        string $relationshipType
    ): void {
        $query = sprintf(
            'MERGE (a:Node {nome: $fromName})
             ON CREATE SET a.uuid = randomUUID()
             MERGE (b:Node {nome: $toName})
             ON CREATE SET b.uuid = randomUUID()
             MERGE (a)-[:%s]->(b)',
            $relationshipType
        );

        Database::client()->run($query, [
            'fromName' => $fromName,
            'toName' => $toName,
        ]);
    }
}
