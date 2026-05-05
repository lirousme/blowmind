const schemaFeedback = document.getElementById('schemaFeedback');
const lists = {
  nodes: document.getElementById('nodeList'),
  relationships: document.getElementById('relationshipList'),
  propertyKeys: document.getElementById('propertyList'),
};

function normalizeBasePath(pathname) {
  const trimmed = pathname.replace(/\/+$/, '');
  if (trimmed.endsWith('/index.php/schema')) {
    return trimmed.slice(0, -'/index.php/schema'.length) || '';
  }

  if (trimmed.endsWith('/schema')) {
    return trimmed.slice(0, -'/schema'.length) || '';
  }

  if (trimmed.endsWith('/index.php')) {
    return trimmed.slice(0, -'/index.php'.length) || '';
  }

  return trimmed;
}

const basePath = normalizeBasePath(window.location.pathname);

function route(path) {
  return `${basePath}/index.php${path}`;
}

function setFeedback(message, isSuccess = true) {
  schemaFeedback.textContent = message;
  schemaFeedback.className = isSuccess ? 'text-emerald-400 text-sm' : 'text-rose-400 text-sm';
}

function renderList(list, items) {
  list.innerHTML = '';

  if (items.length === 0) {
    const empty = document.createElement('li');
    empty.className = 'text-slate-500 italic';
    empty.textContent = 'Nenhum item encontrado.';
    list.appendChild(empty);
    return;
  }

  items.forEach((item) => {
    const li = document.createElement('li');
    li.className = 'bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 break-words';
    li.textContent = item;
    list.appendChild(li);
  });
}

async function loadSchemaItems() {
  const response = await fetch(route('/schema-items'));
  const payload = await response.json();
  const schema = payload.schema || {};

  renderList(lists.nodes, schema.nodes || []);
  renderList(lists.relationships, schema.relationships || []);
  renderList(lists.propertyKeys, schema.propertyKeys || []);
}

async function addSchemaItem(form) {
  const data = new FormData(form);
  data.append('kind', form.dataset.kind);

  const response = await fetch(route('/schema-items'), {
    method: 'POST',
    body: data,
  });
  const payload = await response.json();

  setFeedback(payload.message || 'Sem resposta', response.ok);

  if (response.ok) {
    form.reset();
    await loadSchemaItems();
  }
}

document.querySelectorAll('.schema-form').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    await addSchemaItem(event.currentTarget);
  });
});

loadSchemaItems().catch(() => {
  setFeedback('Não foi possível carregar os itens do banco.', false);
});
