define(['jquery', 'pim/form-builder', 'pim/login-app'], function ($, formBuilder, PimLoginApp) {
  const loginAppElement = document.querySelector('.login-app');

  console.log('on loading', loginAppElement);
  if (loginAppElement) {
    const loginApp = new PimLoginApp(loginAppElement);
    loginApp.render();

    return;
  }

  formBuilder.build('pim-app').then(function (form) {
    form.setElement($('.app'));
    form.render();
  });
});
