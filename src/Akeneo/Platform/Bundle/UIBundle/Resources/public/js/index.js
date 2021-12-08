// define(['jquery', 'pim/form-builder'], function ($, formBuilder) {
//   formBuilder.build('pim-app').then(function (form) {
//     form.setElement($('.app'));
//     form.render();
//   });
// });

const formBuilder = require('pim/form-builder');

window.pimLegacy = formBuilder;
