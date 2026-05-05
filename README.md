# Blowmind - MVC + Neo4j

Estrutura inicial com:
- POO + MVC
- PHP + JavaScript puro
- TailwindCSS em dark mode
- Diretório `view` com páginas

## Primeira view
A primeira view está em `view/graph-editor.php` e permite:
- Criar nodes
- Criar relações

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
