<?php
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($baseUrl === '' || $baseUrl === '.') {
    $baseUrl = '';
}
?>
<!doctype html>
<html lang="pt-BR" class="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Catálogo do Grafo • Neo4j</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
      };
    </script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <main class="max-w-6xl mx-auto px-4 py-10 space-y-8">
      <header class="space-y-3">
        <a href="<?= htmlspecialchars($baseUrl . '/index.php', ENT_QUOTES) ?>" class="text-sm text-emerald-400 hover:text-emerald-300">← Voltar ao editor</a>
        <div>
          <h1 class="text-3xl font-bold">Catálogo do grafo</h1>
          <p class="text-slate-400 mt-2">Veja e cadastre os tokens de schema já existentes no banco atual.</p>
        </div>
      </header>

      <section class="grid gap-5 md:grid-cols-3" aria-label="Itens do banco atual">
        <article class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
          <div>
            <h2 class="text-xl font-semibold">Nodes</h2>
            <p class="text-sm text-slate-400">Labels cadastradas no Neo4j.</p>
          </div>
          <form class="schema-form flex gap-2" data-kind="node">
            <input name="name" class="min-w-0 flex-1 bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Nova label" required />
            <button class="bg-emerald-600 hover:bg-emerald-500 px-3 py-2 rounded-xl" type="submit">Adicionar</button>
          </form>
          <ul id="nodeList" class="schema-list space-y-2 text-sm"></ul>
        </article>

        <article class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
          <div>
            <h2 class="text-xl font-semibold">Relationships</h2>
            <p class="text-sm text-slate-400">Tipos de relação cadastrados no Neo4j.</p>
          </div>
          <form class="schema-form flex gap-2" data-kind="relationship">
            <input name="name" class="min-w-0 flex-1 bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Novo tipo" required />
            <button class="bg-emerald-600 hover:bg-emerald-500 px-3 py-2 rounded-xl" type="submit">Adicionar</button>
          </form>
          <ul id="relationshipList" class="schema-list space-y-2 text-sm"></ul>
        </article>

        <article class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
          <div>
            <h2 class="text-xl font-semibold">Property keys</h2>
            <p class="text-sm text-slate-400">Chaves de propriedades cadastradas no Neo4j.</p>
          </div>
          <form class="schema-form flex gap-2" data-kind="property">
            <input name="name" class="min-w-0 flex-1 bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Nova chave" required />
            <button class="bg-emerald-600 hover:bg-emerald-500 px-3 py-2 rounded-xl" type="submit">Adicionar</button>
          </form>
          <ul id="propertyList" class="schema-list space-y-2 text-sm"></ul>
        </article>
      </section>

      <div id="schemaFeedback" class="text-sm text-slate-300"></div>
    </main>

    <div id="schemaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 px-4" role="dialog" aria-modal="true" aria-labelledby="schemaModalTitle">
      <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 id="schemaModalTitle" class="text-xl font-semibold">Configurar item</h2>
            <p id="schemaModalDescription" class="mt-1 text-sm text-slate-400">Altere o nome ou exclua este item do schema.</p>
          </div>
          <button id="schemaModalClose" class="rounded-lg px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-100" type="button" aria-label="Fechar modal">×</button>
        </div>

        <form id="schemaEditForm" class="mt-5 space-y-4">
          <input type="hidden" name="kind" id="schemaEditKind" />
          <input type="hidden" name="oldName" id="schemaEditOldName" />

          <label class="block space-y-2">
            <span class="text-sm font-medium text-slate-300">Nome</span>
            <input name="newName" id="schemaEditNewName" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3" required />
          </label>

          <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
            <button id="schemaDeleteButton" class="rounded-xl border border-rose-500/60 px-4 py-2 text-rose-300 hover:bg-rose-500/10" type="button">Excluir</button>
            <div class="flex gap-3">
              <button id="schemaCancelButton" class="rounded-xl border border-slate-700 px-4 py-2 text-slate-300 hover:bg-slate-800" type="button">Cancelar</button>
              <button class="rounded-xl bg-emerald-600 px-4 py-2 hover:bg-emerald-500" type="submit">Salvar</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script src="<?= htmlspecialchars($baseUrl . '/assets/js/schema-catalog.js', ENT_QUOTES) ?>"></script>
  </body>
</html>
