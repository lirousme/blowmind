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

    public function storeNode(): void
    {
        $label = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['label'] ?? ''));
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
            'relationshipType' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['relationshipType'] ?? '')),
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

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
