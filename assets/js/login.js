// Sign-in page: show or hide the password.
const button = document.querySelector('[data-reveal]');
const input = document.querySelector('input[name="password"]');
button?.addEventListener('click', () => {
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  button.querySelector('use').setAttribute('href', button.querySelector('use').getAttribute('href').replace(/#.*/, show ? '#eye-off' : '#eye'));
  input.focus();
});
