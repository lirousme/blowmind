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
    <title>Graph Editor • Neo4j</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
      };
    </script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <main class="max-w-4xl mx-auto px-4 py-10 space-y-8">
      <header class="space-y-3">
        <a href="<?= htmlspecialchars($baseUrl . '/index.php/schema', ENT_QUOTES) ?>" class="text-sm text-emerald-400 hover:text-emerald-300">Abrir catálogo do grafo →</a>
        <div>
          <h1 class="text-3xl font-bold">Neo4j Graph Editor</h1>
          <p class="text-slate-400 mt-2">Digite dois nomes, escolha o tipo de relação e o sistema cria/usa os nodes automaticamente com UUID.</p>
        </div>
      </header>

      <section>
        <form id="relForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 max-w-2xl">
          <h2 class="text-xl font-semibold">Criar relação por nome</h2>

          <div class="space-y-1">
            <label for="fromName" class="text-sm text-slate-300">Nome do primeiro node</label>
            <input id="fromName" name="fromName" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Ex.: Morango" required />
          </div>

          <div class="space-y-1">
            <label for="relationshipType" class="text-sm text-slate-300">Tipo de relação existente</label>
            <select name="relationshipType" id="relationshipType" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" required>
              <option value="">Selecione um tipo de relação</option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="toName" class="text-sm text-slate-300">Nome do segundo node</label>
            <input id="toName" name="toName" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Ex.: Doce" required />
          </div>

          <p class="text-xs text-slate-400">UUID é gerado automaticamente quando o node ainda não existe.</p>
          <button class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-xl">Criar Relação</button>
        </form>
      </section>

      <div id="feedback" class="text-sm text-slate-300"></div>
    </main>

    <script src="<?= htmlspecialchars($baseUrl . '/assets/js/graph-editor.js', ENT_QUOTES) ?>"></script>
  </body>
</html>
