define([
  'jquery',
  'underscore',
  'backbone',
  'oro/messenger',
  'oro/error',
  'routing',
  'react',
  'akeneo-design-system',
], function ($, _, Backbone, messenger, Error, Routing, React, {Link}) {
  return Backbone.View.extend({
    action: null,

    jobExportLinkTemplate: '<a href="<%= url %>"><%= label %></a>',

    initialize: function (action) {
      this.action = action;
    },

    run: function () {
      const JobExportLinkTemplate = this.jobExportLinkTemplate;

      $.get(this.action.getLinkWithParameters())
        .done(function (data) {
          const jobUrl = '#' + Routing.generate('pim_enrich_job_tracker_show', {id: data.job_id});
          const title = _.__('pim_datagrid.mass_action.quick_export.success');

          const message = React.createElement(
            'span',
            null,
            _.__('pim_datagrid.mass_action.quick_export.flash.message')
          );
          const link = React.createElement(
            Link,
            {href: jobUrl},
            _.__('pim_datagrid.mass_action.quick_export.flash.link')
          );
          const children = React.createElement('span', null, message, link);

          messenger.notify({level: 'success', title, children});
        })
        .fail(function (jqXHR) {
          if (jqXHR.status === 401) {
            Error.dispatch(null, jqXHR);
          } else {
            messenger.notify({level: 'error', title: _.__(jqXHR.responseText)});
          }
        });
    },
  });
});
