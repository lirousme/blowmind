const feedback = document.getElementById('feedback');

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

document.getElementById('nodeForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  await sendForm(event.currentTarget, '/node');
});

document.getElementById('relForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  await sendForm(event.currentTarget, '/relationship');
});
