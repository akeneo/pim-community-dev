define([
  'jquery',
  'oro/translator',
  'backbone',
  'oro/messenger',
  'oro/error',
  'routing',
  'react',
  'akeneo-design-system',
], function ($, __, Backbone, messenger, Error, Routing, React, {Link}) {
  return Backbone.View.extend({
    action: null,

    initialize: function (action) {
      this.action = action;
    },

    run: function () {
      $.get(this.action.getLinkWithParameters())
        .done(function (data) {
          const title = __('pim_datagrid.mass_action.quick_export.success');
          const link = React.createElement(
            Link,
            {key: data.job_id, href: `#${Routing.generate('pim_enrich_job_tracker_show', {id: data.job_id})}`},
            __('pim_datagrid.mass_action.quick_export.flash.message')
          );

          messenger.notify('success', title, link);
        })
        .fail(function (jqXHR) {
          if (jqXHR.status === 401) {
            Error.dispatch(null, jqXHR);
          } else {
            messenger.notify('error', __(jqXHR.responseText));
          }
        });
    },
  });
});
