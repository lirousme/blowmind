<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\GraphModel;

final class GraphController
{
    private GraphModel $graphModel;

    public function __construct()
    {
        $this->graphModel = new GraphModel();
    }

    public function index(): void
    {
        View::render('graph-editor');
    }

    public function schema(): void
    {
        View::render('schema-catalog');
    }

    public function storeNode(): void
    {
        $label = $this->sanitizeIdentifier((string) ($_POST['label'] ?? ''));
        $properties = json_decode((string) ($_POST['properties'] ?? '{}'), true) ?? [];

        if ($label === '') {
            $this->json(['ok' => false, 'message' => 'Label é obrigatório.'], 422);
            return;
        }

        $this->graphModel->createNode($label, is_array($properties) ? $properties : []);
        $this->json(['ok' => true, 'message' => 'Node criado com sucesso.']);
    }

    public function storeRelationship(): void
    {
        $input = [
            'fromName' => trim((string) ($_POST['fromName'] ?? '')),
            'toName' => trim((string) ($_POST['toName'] ?? '')),
            'relationshipType' => $this->sanitizeIdentifier((string) ($_POST['relationshipType'] ?? '')),
        ];

        foreach (['fromName', 'toName', 'relationshipType'] as $requiredField) {
            $value = $input[$requiredField];
            if ($value === '') {
                $this->json(['ok' => false, 'message' => 'Todos os campos são obrigatórios.'], 422);
                return;
            }
        }

        $this->graphModel->createRelationshipByName(
            $input['fromName'],
            $input['toName'],
            $input['relationshipType']
        );

        $this->json(['ok' => true, 'message' => 'Relação criada com sucesso.']);
    }

    public function relationshipTypes(): void
    {
        $this->json(['ok' => true, 'types' => $this->graphModel->getRelationshipTypes()]);
    }

    public function nodeNames(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $this->json(['ok' => true, 'names' => $this->graphModel->findNamesByPrefix($query)]);
    }

    public function schemaItems(): void
    {
        $this->json(['ok' => true, 'schema' => $this->graphModel->getSchemaItems()]);
    }

    public function storeSchemaItem(): void
    {
        $kind = (string) ($_POST['kind'] ?? '');
        $name = $this->sanitizeIdentifier((string) ($_POST['name'] ?? ''));

        if (!$this->isValidSchemaKind($kind)) {
            $this->json(['ok' => false, 'message' => 'Tipo de item inválido.'], 422);
            return;
        }

        if ($name === '') {
            $this->json([
                'ok' => false,
                'message' => 'Use apenas letras, números e underscore, começando por letra ou underscore.',
            ], 422);
            return;
        }

        $this->graphModel->createSchemaItem($kind, $name);
        $this->json(['ok' => true, 'message' => 'Item adicionado com sucesso.']);
    }

    public function updateSchemaItem(): void
    {
        $kind = (string) ($_POST['kind'] ?? '');
        $oldName = $this->sanitizeIdentifier((string) ($_POST['oldName'] ?? ''));
        $newName = $this->sanitizeIdentifier((string) ($_POST['newName'] ?? ''));

        if (!$this->isValidSchemaKind($kind)) {
            $this->json(['ok' => false, 'message' => 'Tipo de item inválido.'], 422);
            return;
        }

        if ($oldName === '' || $newName === '') {
            $this->json([
                'ok' => false,
                'message' => 'Use apenas letras, números e underscore, começando por letra ou underscore.',
            ], 422);
            return;
        }

        $this->graphModel->renameSchemaItem($kind, $oldName, $newName);
        $this->json(['ok' => true, 'message' => 'Item atualizado com sucesso.']);
    }

    public function deleteSchemaItem(): void
    {
        $kind = (string) ($_POST['kind'] ?? '');
        $name = $this->sanitizeIdentifier((string) ($_POST['name'] ?? ''));

        if (!$this->isValidSchemaKind($kind)) {
            $this->json(['ok' => false, 'message' => 'Tipo de item inválido.'], 422);
            return;
        }

        if ($name === '') {
            $this->json([
                'ok' => false,
                'message' => 'Use apenas letras, números e underscore, começando por letra ou underscore.',
            ], 422);
            return;
        }

        $this->graphModel->deleteSchemaItem($kind, $name);
        $this->json(['ok' => true, 'message' => 'Item excluído com sucesso.']);
    }

    private function isValidSchemaKind(string $kind): bool
    {
        return in_array($kind, ['node', 'relationship', 'property'], true);
    }

    private function sanitizeIdentifier(string $value): string
    {
        $identifier = preg_replace('/[^A-Za-z0-9_]/', '', trim($value)) ?? '';

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            return '';
        }

        return $identifier;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
