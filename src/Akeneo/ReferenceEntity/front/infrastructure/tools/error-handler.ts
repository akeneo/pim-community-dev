const router = require('pim/router');

export class BackendError extends Error {
  constructor(public request: any) {
    super(request.statusText);
  }
}

export default (request: any) => {
  if (400 === request.status) {
    return request.responseJSON;
  }
  if (401 === request.status) {
    router.redirectToRoute('pim_user_security_login');
    location.reload();

    throw new BackendError(request);
  }

  throw new BackendError(request);
};
