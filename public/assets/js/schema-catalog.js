const schemaFeedback = document.getElementById('schemaFeedback');
const lists = {
  nodes: document.getElementById('nodeList'),
  relationships: document.getElementById('relationshipList'),
  propertyKeys: document.getElementById('propertyList'),
};
const modal = document.getElementById('schemaModal');
const modalTitle = document.getElementById('schemaModalTitle');
const modalDescription = document.getElementById('schemaModalDescription');
const modalClose = document.getElementById('schemaModalClose');
const cancelButton = document.getElementById('schemaCancelButton');
const deleteButton = document.getElementById('schemaDeleteButton');
const editForm = document.getElementById('schemaEditForm');
const editKind = document.getElementById('schemaEditKind');
const editOldName = document.getElementById('schemaEditOldName');
const editNewName = document.getElementById('schemaEditNewName');

const schemaKindLabels = {
  node: 'node',
  relationship: 'relationship',
  property: 'property key',
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

function renderList(list, items, kind) {
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
    li.className = 'flex items-center justify-between gap-3 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2';

    const name = document.createElement('span');
    name.className = 'min-w-0 break-words';
    name.textContent = item;

    const configButton = document.createElement('button');
    configButton.className = 'shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-emerald-300';
    configButton.type = 'button';
    configButton.dataset.kind = kind;
    configButton.dataset.name = item;
    configButton.setAttribute('aria-label', `Configurar ${item}`);
    configButton.innerHTML = '<svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1.82V22a2 2 0 1 1-4 0v-.18A1.65 1.65 0 0 0 8.6 20a1.65 1.65 0 0 0-1.82-.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.82-.33H2a2 2 0 1 1 0-4h.18A1.65 1.65 0 0 0 4 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8.6 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .33-1.82V2a2 2 0 1 1 4 0v.18A1.65 1.65 0 0 0 15.4 4a1.65 1.65 0 0 0 1.82.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 8.6a1.65 1.65 0 0 0 .6 1 1.65 1.65 0 0 0 1.82.33H22a2 2 0 1 1 0 4h-.18A1.65 1.65 0 0 0 20 15.4Z"/></svg>';
    configButton.addEventListener('click', () => openSchemaModal(kind, item));

    li.append(name, configButton);
    list.appendChild(li);
  });
}

function openSchemaModal(kind, name) {
  editKind.value = kind;
  editOldName.value = name;
  editNewName.value = name;
  modalTitle.textContent = `Configurar ${schemaKindLabels[kind]}`;
  modalDescription.textContent = `Altere o nome de "${name}" ou exclua este item.`;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  editNewName.focus();
  editNewName.select();
}

function closeSchemaModal() {
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  editForm.reset();
}

async function requestSchemaAction(path, data) {
  const response = await fetch(route(path), {
    method: 'POST',
    body: data,
  });
  const payload = await response.json();

  setFeedback(payload.message || 'Sem resposta', response.ok);

  if (response.ok) {
    closeSchemaModal();
    await loadSchemaItems();
  }
}

async function loadSchemaItems() {
  const response = await fetch(route('/schema-items'));
  const payload = await response.json();
  const schema = payload.schema || {};

  renderList(lists.nodes, schema.nodes || [], 'node');
  renderList(lists.relationships, schema.relationships || [], 'relationship');
  renderList(lists.propertyKeys, schema.propertyKeys || [], 'property');
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

editForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  await requestSchemaAction('/schema-items/update', new FormData(editForm));
});

deleteButton.addEventListener('click', async () => {
  const itemName = editOldName.value;
  const itemKind = editKind.value;
  const confirmationMessage = itemKind === 'node'
    ? `Excluir todos os nodes do tipo "${itemName}" e suas relações?`
    : `Excluir "${itemName}"? Essa ação altera os dados que usam este item.`;
  const confirmed = window.confirm(confirmationMessage);

  if (!confirmed) {
    return;
  }

  const data = new FormData();
  data.append('kind', itemKind);
  data.append('name', itemName);
  await requestSchemaAction('/schema-items/delete', data);
});

[modalClose, cancelButton].forEach((button) => {
  button.addEventListener('click', closeSchemaModal);
});

modal.addEventListener('click', (event) => {
  if (event.target === modal) {
    closeSchemaModal();
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
    closeSchemaModal();
  }
});

loadSchemaItems().catch(() => {
  setFeedback('Não foi possível carregar os itens do banco.', false);
});
