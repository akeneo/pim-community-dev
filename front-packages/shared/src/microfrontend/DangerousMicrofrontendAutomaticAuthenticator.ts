let authentication_in_progress = false;

export const DangerousMicrofrontendAutomaticAuthenticator = {
  enable: (username = 'admin', password = 'admin') => {
    window.addEventListener('unhandledrejection', (e: PromiseRejectionEvent): void => {
      if (e.reason?.toString() !== 'Error: You are not logged in the PIM') {
        return;
      }

      if (authentication_in_progress) {
        return;
      }

      authentication_in_progress = true;

      fetch('/user/login')
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const dom = parser.parseFromString(html, 'text/html');
          const input = dom.querySelector('input[name="_csrf_token"]');
          const csrf = input?.getAttribute('value');

          if (!csrf) {
            console.error('Cannot find a CSRF token in the login page');

            return;
          }

          const form = new FormData();
          form.append('_username', username);
          form.append('_password', password);
          form.append('_submit', '');
          form.append('_target_path', '');
          form.append('_csrf_token', csrf);

          fetch('/user/login-check', {
            method: 'POST',
            body: form,
          })
            // It's not the keyword "then" because the browsers are unhappy with the redirection
            // response to a different domain (CORS). Response or error are both opaques.
            // Using another request in "finally" is the only available way to check either the authentication
            // was successful or not.
            .finally(() => {
              fetch('/rest/user/').then(response => {
                if (response.ok) {
                  location.reload();
                } else {
                  console.error(`Cannot login automatically with the credentials: ${username}/${password}`);
                }
              });
            });
        });
    });
  },
};
