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
            'fromLabel' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['fromLabel'] ?? '')),
            'fromKey' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['fromKey'] ?? '')),
            'fromValue' => (string) ($_POST['fromValue'] ?? ''),
            'toLabel' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['toLabel'] ?? '')),
            'toKey' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['toKey'] ?? '')),
            'toValue' => (string) ($_POST['toValue'] ?? ''),
            'relationshipType' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['relationshipType'] ?? '')),
        ];

        foreach ($input as $value) {
            if ($value === '') {
                $this->json(['ok' => false, 'message' => 'Todos os campos são obrigatórios.'], 422);
                return;
            }
        }

        $this->graphModel->createRelationship(
            $input['fromLabel'],
            $input['fromKey'],
            $input['fromValue'],
            $input['toLabel'],
            $input['toKey'],
            $input['toValue'],
            $input['relationshipType']
        );

        $this->json(['ok' => true, 'message' => 'Relação criada com sucesso.']);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
