
define(['jquery', 'pim/form-builder', 'react'], function ($, formBuilder, React) {
  React.lazy(
    () => import(/* webpackChunkName: "measurement" */ '@akeneo-pim-community/measurement/lib/MeasurementApp')
  );
  // console.log(MeasurementApp)
  formBuilder.build('pim-app').then(function (form) {
    form.setElement($('.app'));
    form.render();
  });
});
