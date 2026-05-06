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
            'MATCH (n:Node) WHERE n.nome IS NOT NULL AND n.nome STARTS WITH $query RETURN DISTINCT n.nome AS nome ORDER BY nome LIMIT $limit',
            ['query' => $query, 'limit' => $limit]
        );

        return array_values(array_filter(array_map(
            static fn ($record): string => (string) $record->get('nome'),
            iterator_to_array($result)
        )));
    }

    public function getRelationshipTypes(): array
    {
        $relationshipTypes = $this->collectProcedureColumn(
            'MATCH ()-[relationship]->() RETURN DISTINCT type(relationship) AS relationshipType ORDER BY relationshipType',
            'relationshipType'
        );

        if ($relationshipTypes === []) {
            $relationshipTypes = $this->collectProcedureColumn(
                'CALL db.relationshipTypes() YIELD relationshipType RETURN relationshipType ORDER BY relationshipType',
                'relationshipType'
            );
        }

        return $this->mergeSchemaCatalogItems('relationship', $relationshipTypes);
    }

    public function getNodeLabels(): array
    {
        $nodeLabels = $this->collectProcedureColumn(
            'MATCH (node) WHERE NOT node:__BlowmindSchemaItem UNWIND labels(node) AS label RETURN DISTINCT label ORDER BY label',
            'label'
        );

        if ($nodeLabels === []) {
            $nodeLabels = array_values(array_diff(
                $this->collectProcedureColumn(
                    'CALL db.labels() YIELD label RETURN label ORDER BY label',
                    'label'
                ),
                ['__BlowmindSchemaItem']
            ));
        }

        return $this->mergeSchemaCatalogItems('node', $nodeLabels);
    }

    public function getPropertyKeys(): array
    {
        $nodeProperties = $this->collectProcedureColumn(
            'MATCH (node) WHERE NOT node:__BlowmindSchemaItem UNWIND keys(node) AS propertyKey RETURN DISTINCT propertyKey',
            'propertyKey'
        );
        $relationshipProperties = $this->collectProcedureColumn(
            'MATCH ()-[relationship]->() UNWIND keys(relationship) AS propertyKey RETURN DISTINCT propertyKey',
            'propertyKey'
        );

        $propertyKeys = array_merge($nodeProperties, $relationshipProperties);

        if ($propertyKeys === []) {
            $propertyKeys = $this->collectProcedureColumn(
                'CALL db.propertyKeys() YIELD propertyKey RETURN propertyKey ORDER BY propertyKey',
                'propertyKey'
            );
        }

        return $this->mergeSchemaCatalogItems('property', $propertyKeys);
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
        if (!$this->isValidSchemaKind($kind)) {
            return;
        }

        Database::client()->run(
            'MERGE (item:__BlowmindSchemaItem {kind: $kind, name: $name})
             ON CREATE SET item.uuid = randomUUID(), item.createdAt = datetime()
             SET item.updatedAt = datetime()',
            ['kind' => $kind, 'name' => $name]
        );
    }

    public function renameSchemaItem(string $kind, string $oldName, string $newName): void
    {
        if (!$this->isValidSchemaKind($kind) || $oldName === $newName) {
            return;
        }

        $old = $this->quoteIdentifier($oldName);
        $new = $this->quoteIdentifier($newName);

        if ($kind === 'node') {
            Database::client()->run(sprintf('MATCH (node:%s) SET node:%s REMOVE node:%s', $old, $new, $old));
            $this->renameSchemaCatalogItem($kind, $oldName, $newName);
            return;
        }

        if ($kind === 'relationship') {
            Database::client()->run(sprintf(
                'MATCH (source)-[relationship:%s]->(target)
                 CREATE (source)-[renamed:%s]->(target)
                 SET renamed = properties(relationship)
                 DELETE relationship',
                $old,
                $new
            ));
            $this->renameSchemaCatalogItem($kind, $oldName, $newName);
            return;
        }

        if ($kind === 'property') {
            Database::client()->run(sprintf(
                'MATCH (node) WHERE node.%s IS NOT NULL SET node.%s = node.%s REMOVE node.%s',
                $old,
                $new,
                $old,
                $old
            ));
            Database::client()->run(sprintf(
                'MATCH ()-[relationship]->() WHERE relationship.%s IS NOT NULL SET relationship.%s = relationship.%s REMOVE relationship.%s',
                $old,
                $new,
                $old,
                $old
            ));
            $this->renameSchemaCatalogItem($kind, $oldName, $newName);
        }
    }

    public function deleteSchemaItem(string $kind, string $name): void
    {
        if (!$this->isValidSchemaKind($kind)) {
            return;
        }

        $identifier = $this->quoteIdentifier($name);
        $this->deleteSchemaCatalogItem($kind, $name);

        if ($kind === 'node') {
            Database::client()->run(sprintf('MATCH (node:%s) DETACH DELETE node', $identifier));
            return;
        }

        if ($kind === 'relationship') {
            Database::client()->run(sprintf('MATCH ()-[relationship:%s]-() DELETE relationship', $identifier));
            return;
        }

        if ($kind === 'property') {
            Database::client()->run(sprintf('MATCH (node) REMOVE node.%s', $identifier));
            Database::client()->run(sprintf('MATCH ()-[relationship]->() REMOVE relationship.%s', $identifier));
        }
    }

    private function mergeSchemaCatalogItems(string $kind, array $databaseItems): array
    {
        $items = array_merge($databaseItems, $this->getSchemaCatalogItems($kind));
        $items = array_values(array_unique(array_filter(
            $items,
            static fn (string $item): bool => $item !== ''
        )));
        sort($items, SORT_NATURAL | SORT_FLAG_CASE);

        return $items;
    }

    private function getSchemaCatalogItems(string $kind): array
    {
        return $this->collectProcedureColumn(
            'MATCH (item:__BlowmindSchemaItem {kind: $kind}) RETURN DISTINCT item.name AS name ORDER BY name',
            'name',
            ['kind' => $kind]
        );
    }

    private function renameSchemaCatalogItem(string $kind, string $oldName, string $newName): void
    {
        Database::client()->run(
            'MERGE (renamed:__BlowmindSchemaItem {kind: $kind, name: $newName})
             ON CREATE SET renamed.uuid = randomUUID(), renamed.createdAt = datetime()
             SET renamed.updatedAt = datetime()
             WITH renamed
             MATCH (old:__BlowmindSchemaItem {kind: $kind, name: $oldName})
             WHERE old <> renamed
             DETACH DELETE old',
            ['kind' => $kind, 'oldName' => $oldName, 'newName' => $newName]
        );
    }

    private function deleteSchemaCatalogItem(string $kind, string $name): void
    {
        Database::client()->run(
            'MATCH (item:__BlowmindSchemaItem {kind: $kind, name: $name}) DETACH DELETE item',
            ['kind' => $kind, 'name' => $name]
        );
    }

    private function isValidSchemaKind(string $kind): bool
    {
        return in_array($kind, ['node', 'relationship', 'property'], true);
    }

    private function collectProcedureColumn(string $query, string $column, array $parameters = []): array
    {
        $result = Database::client()->run($query, $parameters);
        $values = [];

        foreach ($result as $record) {
            $value = $record->get($column);

            if ($value === null) {
                continue;
            }

            $stringValue = is_scalar($value) || $value instanceof \Stringable
                ? trim((string) $value)
                : '';

            if ($stringValue !== '') {
                $values[] = $stringValue;
            }
        }

        return array_values(array_unique($values));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public function createNode(string $label, array $properties): void
    {
        $query = sprintf('CREATE (n:%s $props)', $this->quoteIdentifier($label));
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
            $this->quoteIdentifier($relationshipType)
        );

        Database::client()->run($query, [
            'fromName' => $fromName,
            'toName' => $toName,
        ]);
    }
}
