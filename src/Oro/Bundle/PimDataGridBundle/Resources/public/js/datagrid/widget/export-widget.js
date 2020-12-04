define(['jquery', 'underscore', 'backbone', 'oro/messenger', 'oro/error', 'routing'], function (
  $,
  _,
  Backbone,
  messenger,
  Error,
  Routing
) {
  'use strict';

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
          const jobLink = _.template(JobExportLinkTemplate)({
            url: jobUrl,
            label: _.__('pim_datagrid.mass_action.quick_export.job_link_label'),
          });
          const messageTitle = _.__('pim_datagrid.mass_action.quick_export.success');
          const message = _.__('pim_datagrid.mass_action.quick_export.success_message', {
            job_link: jobLink,
          });

          messenger.notify('success', message, {
            messageTitle: messageTitle,
          });
        })
        .fail(function (jqXHR) {
          if (jqXHR.status === 401) {
            Error.dispatch(null, jqXHR);
          } else {
            messenger.notify('error', _.__(jqXHR.responseText));
          }
        });
    },
  });
});
