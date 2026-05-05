# Blowmind - MVC + Neo4j

Estrutura inicial com:
- POO + MVC
- PHP + JavaScript puro
- TailwindCSS em dark mode
- Diretório `view` com páginas

## Views
A view principal está em `view/graph-editor.php` e permite:
- Criar nodes
- Criar relações

A view de catálogo está em `view/schema-catalog.php` e permite:
- Listar labels de nodes, tipos de relationships e property keys do banco atual
- Adicionar novas labels, novos tipos de relationships e novas property keys

## Configuração
1. Instale dependências:
   ```bash
   composer install
   ```
2. Rode com servidor embutido do PHP:
   ```bash
   php -S localhost:8000 -t public
   ```
3. Acesse `http://localhost:8000`

## Conexão Neo4j
Configurada em `config/database.php` com:
`bolt://neo4j:75351595@localhost:7687`

## Solução do erro `vendor/autoload.php`
Se aparecer o erro abaixo:

```txt
Failed opening required '.../vendor/autoload.php'
```

significa que as dependências do Composer ainda não foram instaladas no projeto. Rode `composer install` na raiz do projeto.

No Windows (WAMP), abra o terminal na pasta do projeto e execute:

```bash
composer install
php -S localhost:8000 -t public
```

O `public/index.php` já foi preparado para exibir uma mensagem amigável caso o `autoload.php` ainda não exista.
