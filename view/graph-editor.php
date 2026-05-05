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
    <main class="max-w-5xl mx-auto px-4 py-10 space-y-8">
      <header>
        <h1 class="text-3xl font-bold">Neo4j Graph Editor</h1>
        <p class="text-slate-400 mt-2">Crie nodes e relações usando JavaScript puro + PHP (MVC).</p>
      </header>

      <section>
        <form id="relForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 max-w-2xl">
          <h2 class="text-xl font-semibold">Criar relação por nome</h2>
          <input name="fromName" list="fromNameSuggestions" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Nome do primeiro node" required />
          <datalist id="fromNameSuggestions"></datalist>
          <input name="fromUuid" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="UUID do primeiro node (opcional)" />
          <select name="relationshipType" id="relationshipType" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" required>
            <option value="">Selecione um tipo de relação</option>
          </select>
          <input name="toName" list="toNameSuggestions" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Nome do segundo node" required />
          <datalist id="toNameSuggestions"></datalist>
          <input name="toUuid" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="UUID do segundo node (opcional)" />
          <button class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-xl">Criar Relação</button>
        </form>
      </section>

      <div id="feedback" class="text-sm text-slate-300"></div>
    </main>

    <script src="/assets/js/graph-editor.js"></script>
  </body>
</html>
