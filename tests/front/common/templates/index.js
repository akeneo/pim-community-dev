// define(['routing', 'require-polyfill'], function (routing) {
//     routing.setBaseUrl('http://pim.com')
// });

define(['jquery', 'pim/form-builder'], function ($, formBuilder) {
    formBuilder.build('pim-app')
        .then(function (form) {
            form.setElement($('#app'));
            form.render();
        });
});
