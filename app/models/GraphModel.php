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
            'MATCH (n) WHERE n.nome IS NOT NULL AND toLower(toString(n.nome)) STARTS WITH toLower($query) RETURN DISTINCT toString(n.nome) AS nome ORDER BY nome LIMIT $limit',
            ['query' => $query, 'limit' => $limit]
        );

        return array_values(array_filter(array_map(
            static fn ($record): string => (string) $record->get('nome'),
            iterator_to_array($result)
        )));
    }

    public function getRelationshipTypes(): array
    {
        return $this->collectProcedureColumn(
            'CALL db.relationshipTypes() YIELD relationshipType RETURN relationshipType ORDER BY relationshipType',
            'relationshipType'
        );
    }

    public function getNodeLabels(): array
    {
        return $this->collectProcedureColumn(
            'CALL db.labels() YIELD label RETURN label ORDER BY label',
            'label'
        );
    }

    public function getPropertyKeys(): array
    {
        return $this->collectProcedureColumn(
            'CALL db.propertyKeys() YIELD propertyKey RETURN propertyKey ORDER BY propertyKey',
            'propertyKey'
        );
    }

    public function getSchemaItems(): array
    {
        return [
            'nodes' => $this->getNodeLabels(),
            'relationships' => $this->getRelationshipTypes(),
            'propertyKeys' => $this->getPropertyKeys(),
        ];
    }

    public function createSchemaItem(string $kind, string $name): void
    {
        $procedureByKind = [
            'node' => 'db.createLabel',
            'relationship' => 'db.createRelationshipType',
            'property' => 'db.createProperty',
        ];

        $procedure = $procedureByKind[$kind] ?? null;

        if ($procedure === null) {
            return;
        }

        Database::client()->run(sprintf('CALL %s($name)', $procedure), ['name' => $name]);
    }

    private function collectProcedureColumn(string $query, string $column): array
    {
        $result = Database::client()->run($query);

        return array_values(array_filter(array_map(
            static fn ($record): string => (string) $record->get($column),
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
