const router = require('pim/router');

export default (error: any) => {
  if (500 === error.status) {
    throw new Error('Internal Server error');
  }
  if (0 === error.status) {
    throw new Error('Client is offline');
  }
  if (400 === error.status) {
    return error.responseJSON;
  }
  if (401 === error.status) {
    router.redirectToRoute('pim_user_security_login');
    location.reload();

    throw new Error('User not logged in');
  }

  throw new Error(error.responseJSON);
};
