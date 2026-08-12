// /application/static/scripts/auth.js
// Handles login and register forms with client-side validation and AJAX (fetch).

async function postForm(url, formEl) {
  const fd = new FormData(formEl);
  const opts = { method: 'POST', body: fd };
  const res = await fetch(url, opts);
  const text = await res.text();
  // FIX 1: Return an object with both text and HTTP status so callers can distinguish
  //         success from server-side errors. Previously only the body text was returned,
  //         so a 401/500 from the server was silently treated the same as a 200.
  return { text, ok: res.ok, status: res.status };
}

function showFieldMessage(msgEl, text, isSuccess) {
  msgEl.style.display = 'block';
  msgEl.style.color   = isSuccess ? '#a7f3d0' : '#fca5a5';
  msgEl.textContent   = text;
  if (!isSuccess && /suspicious input detected/i.test(text)) {
    alert('Suspicious input detected. Please remove any injection-style characters and try again.');
  }
}

document.addEventListener('DOMContentLoaded', () => {

  // ── Login form ──────────────────────────────────────────────────────────────
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const loginBtn = document.getElementById('loginBtn');

    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const uname = document.getElementById('username').value.trim();
      const pass  = document.getElementById('password').value;  // FIX 2: don't trim passwords
      const msg   = document.getElementById('msg');

      if (!uname || !pass) {
        showFieldMessage(msg, 'Please enter username and password.', false);
        return;
      }

      loginBtn.disabled = true; // FIX 3: Prevent double-submit

      try {
        const { text, ok } = await postForm('../../php/auth/login.php', loginForm);
        showFieldMessage(msg, text, ok);

        // FIX 4: On success, redirect to the appropriate dashboard instead of staying
        //         on the login page (previously the comment said "keep manual for demo"
        //         but there was no redirect at all, leaving users stranded after login).
        if (ok && text.toLowerCase().includes('successful')) {
          // A brief delay so the user reads the success message before navigating
          setTimeout(() => {
            window.location.href = '../dashboard/admin.html';
          }, 800);
        }
      } catch (err) {
        msg.style.display = 'block';
        msg.style.color   = '#fca5a5';
        msg.textContent   = 'Login request failed. Please check your connection.';
      } finally {
        loginBtn.disabled = false;
      }
    });
  }

  // ── Register form ───────────────────────────────────────────────────────────
  const regForm = document.getElementById('registerForm');
  if (regForm) {
    const regBtn = document.getElementById('registerBtn');

    regForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const uname = document.getElementById('rusername').value.trim();
      const pass  = document.getElementById('rpassword').value; // FIX 2: don't trim passwords
      const role  = document.getElementById('role').value;
      const msg   = document.getElementById('rmsg');

      // FIX 5: Validate username length (was only checking password length)
      if (uname.length < 3) {
        showFieldMessage(msg, 'Username must be at least 3 characters.', false);
        return;
      }

      if (pass.length < 6) {
        showFieldMessage(msg, 'Password must be at least 6 characters.', false);
        return;
      }

      regBtn.disabled = true; // FIX 3: Prevent double-submit

      try {
        const { text, ok, status } = await postForm('../../php/auth/register.php', regForm);
        // FIX 6: Show conflict error distinctly (username taken = 409)
        showFieldMessage(
          msg,
          status === 409 ? 'Username already taken. Please choose another.' : text,
          ok
        );

        if (ok) {
          setTimeout(() => {
            window.location.href = 'login.html';
          }, 1200);
        }
      } catch (err) {
        showFieldMessage(msg, 'Registration failed. Please check your connection.', false);
      } finally {
        regBtn.disabled = false;
      }
    });
  }
});
