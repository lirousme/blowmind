const feedback = document.getElementById('feedback');
const basePath = window.location.pathname.replace(/\/$/, '').replace(/\/index\.php$/, '') || '';
const relForm = document.getElementById('relForm');

function route(path) {
  return `${basePath}${path}`;
}

async function sendForm(form, url) {
  const data = new FormData(form);
  const response = await fetch(url, {
    method: 'POST',
    body: data,
  });

  const payload = await response.json();
  feedback.textContent = payload.message || 'Sem resposta';
  feedback.className = response.ok ? 'text-emerald-400 text-sm' : 'text-rose-400 text-sm';
}

async function loadRelationshipTypes() {
  const select = document.getElementById('relationshipType');
  const response = await fetch(route('/relationship-types'));
  const payload = await response.json();

  (payload.types || []).forEach((type) => {
    const option = document.createElement('option');
    option.value = type;
    option.textContent = type;
    select.appendChild(option);
  });
}

async function fetchNames(query) {
  const response = await fetch(`${route('/node-names')}?q=${encodeURIComponent(query)}`);
  const payload = await response.json();
  return payload.names || [];
}

async function updateSuggestions(input, datalistId) {
  const datalist = document.getElementById(datalistId);
  const query = input.value.trim();
  datalist.innerHTML = '';

  if (query.length < 1) return;

  const names = await fetchNames(query);
  names.forEach((name) => {
    const option = document.createElement('option');
    option.value = name;
    datalist.appendChild(option);
  });
}

['fromName', 'toName'].forEach((fieldName) => {
  const input = relForm.elements[fieldName];
  const datalistId = fieldName === 'fromName' ? 'fromNameSuggestions' : 'toNameSuggestions';
  input.addEventListener('input', () => updateSuggestions(input, datalistId));
});

relForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  await sendForm(event.currentTarget, route('/relationship'));
});

loadRelationshipTypes();
