// /application/static/scripts/services.js

// FIX 1: Replaced innerHTML += with safe DOM API calls to prevent XSS.
//         Original code: result.innerHTML += `<div class="service"><h4>${carModel}</h4><p>${desc}</p></div>`
//         Any user input containing <script> or event handlers would execute.

function escapeHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

function createService(e) {
  if (e) e.preventDefault();

  const form      = document.getElementById('createServiceForm');
  const carModel  = document.getElementById('carModel').value.trim();
  const desc      = document.getElementById('serviceDesc').value.trim();
  const result    = document.getElementById('result');
  const msgEl     = document.getElementById('formMsg');

  // FIX 2: Client-side validation was absent — now validates before submission
  if (!carModel || !desc) {
    if (msgEl) { msgEl.textContent = 'Please fill in both fields.'; msgEl.style.display = 'block'; }
    return false;
  }

  // FIX 3: Post to the PHP endpoint instead of only updating the DOM locally.
  //         The old code inserted into the DOM only — the server never stored the request.
  const fd = new FormData();
  fd.append('car_model', carModel);
  fd.append('service_description', desc);

  fetch('../../php/services/create_service.php', { method: 'POST', body: fd })
    .then(r => r.text())
    .then(text => {
      // Render the new item using safe DOM manipulation
      const wrapper = document.createElement('div');
      wrapper.className = 'service';

      const h4 = document.createElement('h4');
      h4.textContent = carModel; // textContent is XSS-safe

      const p = document.createElement('p');
      p.textContent = desc;

      wrapper.appendChild(h4);
      wrapper.appendChild(p);
      result.appendChild(wrapper);

      // Reset form
      form.reset();
      if (msgEl) { msgEl.textContent = text; msgEl.style.display = 'block'; }
    })
    .catch(() => {
      if (msgEl) { msgEl.textContent = 'Failed to create service request.'; msgEl.style.display = 'block'; }
    });

  return false;
}
