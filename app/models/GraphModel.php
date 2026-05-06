<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class GraphModel
{
    private const INTERNAL_SCHEMA_LABEL = '__BlowmindSchemaItem';

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
        return $this->collectProcedureColumn(
            'MATCH ()-[relationship]->() RETURN DISTINCT type(relationship) AS relationshipType ORDER BY relationshipType',
            'relationshipType'
        );
    }

    public function getNodeLabels(): array
    {
        return $this->collectProcedureColumn(
            'MATCH (node)
             WHERE NOT node:' . self::INTERNAL_SCHEMA_LABEL . '
             UNWIND labels(node) AS label
             WITH DISTINCT label
             WHERE label <> $internalLabel
             RETURN label
             ORDER BY label',
            'label',
            ['internalLabel' => self::INTERNAL_SCHEMA_LABEL]
        );
    }

    public function getPropertyKeys(): array
    {
        $nodeProperties = $this->collectProcedureColumn(
            'MATCH (node)
             WHERE NOT node:' . self::INTERNAL_SCHEMA_LABEL . '
             UNWIND keys(node) AS propertyKey
             RETURN DISTINCT propertyKey
             ORDER BY propertyKey',
            'propertyKey'
        );
        $relationshipProperties = $this->collectProcedureColumn(
            'MATCH ()-[relationship]->()
             UNWIND keys(relationship) AS propertyKey
             RETURN DISTINCT propertyKey
             ORDER BY propertyKey',
            'propertyKey'
        );

        $propertyKeys = array_values(array_unique(array_merge($nodeProperties, $relationshipProperties)));
        sort($propertyKeys, SORT_NATURAL | SORT_FLAG_CASE);

        return $propertyKeys;
    }

    public function getSchemaItems(): array
    {
        $this->deleteLegacySchemaCatalogItems();

        return [
            'nodes' => $this->getNodeLabels(),
            'relationships' => $this->getRelationshipTypes(),
            'propertyKeys' => $this->getPropertyKeys(),
        ];
    }

    public function createSchemaItem(string $kind, string $name): bool
    {
        unset($kind, $name);

        return false;
    }

    public function renameSchemaItem(string $kind, string $oldName, string $newName): bool
    {
        if (!$this->isValidSchemaKind($kind) || $oldName === $newName) {
            return false;
        }

        if (!$this->schemaItemExists($kind, $oldName)) {
            return false;
        }

        $old = $this->quoteIdentifier($oldName);
        $new = $this->quoteIdentifier($newName);

        if ($kind === 'node') {
            Database::client()->run(sprintf('MATCH (node:%s) SET node:%s REMOVE node:%s', $old, $new, $old));
            return true;
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
            return true;
        }

        Database::client()->run(sprintf(
            'MATCH (node)
             WHERE $oldName IN keys(node)
             SET node.%s = node.%s
             REMOVE node.%s',
            $new,
            $old,
            $old
        ), ['oldName' => $oldName]);
        Database::client()->run(sprintf(
            'MATCH ()-[relationship]->()
             WHERE $oldName IN keys(relationship)
             SET relationship.%s = relationship.%s
             REMOVE relationship.%s',
            $new,
            $old,
            $old
        ), ['oldName' => $oldName]);

        return true;
    }

    public function deleteSchemaItem(string $kind, string $name): bool
    {
        if (!$this->isValidSchemaKind($kind) || !$this->schemaItemExists($kind, $name)) {
            return false;
        }

        $identifier = $this->quoteIdentifier($name);

        if ($kind === 'node') {
            Database::client()->run(sprintf('MATCH (node:%s) DETACH DELETE node', $identifier));
            return true;
        }

        if ($kind === 'relationship') {
            Database::client()->run(sprintf('MATCH ()-[relationship:%s]-() DELETE relationship', $identifier));
            return true;
        }

        Database::client()->run(sprintf('MATCH (node) REMOVE node.%s', $identifier));
        Database::client()->run(sprintf('MATCH ()-[relationship]->() REMOVE relationship.%s', $identifier));

        return true;
    }

    private function schemaItemExists(string $kind, string $name): bool
    {
        $identifier = $this->quoteIdentifier($name);

        if ($kind === 'node') {
            return $this->countQuery(sprintf('MATCH (node:%s) RETURN count(node) AS total', $identifier)) > 0;
        }

        if ($kind === 'relationship') {
            return $this->countQuery(sprintf('MATCH ()-[relationship:%s]-() RETURN count(relationship) AS total', $identifier)) > 0;
        }

        return $this->countQuery(
            'MATCH (node) WHERE $name IN keys(node) RETURN count(node) AS total',
            ['name' => $name]
        ) > 0 || $this->countQuery(
            'MATCH ()-[relationship]->() WHERE $name IN keys(relationship) RETURN count(relationship) AS total',
            ['name' => $name]
        ) > 0;
    }

    private function deleteLegacySchemaCatalogItems(): void
    {
        Database::client()->run('MATCH (item:' . self::INTERNAL_SCHEMA_LABEL . ') DETACH DELETE item');
    }

    private function isValidSchemaKind(string $kind): bool
    {
        return in_array($kind, ['node', 'relationship', 'property'], true);
    }

    private function countQuery(string $query, array $parameters = []): int
    {
        $result = Database::client()->run($query, $parameters);

        foreach ($result as $record) {
            return (int) $record->get('total');
        }

        return 0;
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

    private function quoteStringLiteral(string $value): string
    {
        return "'" . str_replace("'", "\\'", $value) . "'";
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
