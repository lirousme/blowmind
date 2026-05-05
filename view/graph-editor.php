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

      <section class="grid md:grid-cols-2 gap-6">
        <form id="nodeForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
          <h2 class="text-xl font-semibold">Criar Node</h2>
          <div>
            <label class="block mb-1 text-sm">Label</label>
            <input name="label" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Pessoa" required />
          </div>
          <div>
            <label class="block mb-1 text-sm">Propriedades (JSON)</label>
            <textarea name="properties" rows="5" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder='{"nome":"Ana","idade":30}'></textarea>
          </div>
          <button class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-xl">Criar Node</button>
        </form>

        <form id="relForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
          <h2 class="text-xl font-semibold">Criar Relação</h2>
          <input name="fromLabel" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Label origem" required />
          <input name="fromKey" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Chave origem (ex: nome)" required />
          <input name="fromValue" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Valor origem" required />
          <input name="toLabel" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Label destino" required />
          <input name="toKey" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Chave destino" required />
          <input name="toValue" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="Valor destino" required />
          <input name="relationshipType" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2" placeholder="TIPO_RELACAO" required />
          <button class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-xl">Criar Relação</button>
        </form>
      </section>

      <div id="feedback" class="text-sm text-slate-300"></div>
    </main>

    <script src="/assets/js/graph-editor.js"></script>
  </body>
</html>
