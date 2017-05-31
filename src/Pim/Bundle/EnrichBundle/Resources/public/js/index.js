define(['jquery', 'pim/form-builder'], function ($, formBuilder) {
    if (module.hot) {
     module.hot.accept();
   }

    formBuilder.build('pim-app')
        .then(function (form) {
            form.setElement($('.app'));
            form.render();
        });
});
