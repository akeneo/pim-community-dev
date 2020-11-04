'use strict';

/**
 * Project due date.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'pim/form',
  'backbone',
  'pim/formatter/date',
  'pim/date-context',
  'teamwork-assistant/templates/widget/project-due-date',
], function($, _, __, BaseForm, Backbone, DateFormatter, DateContext, template) {
  return BaseForm.extend({
    template: _.template(template),

    /**
     * Render the due date from the model
     */
    render: function() {
      var localizedDueDate = DateFormatter.format(
        this.getFormData().currentProject.due_date,
        'yyyy-MM-dd',
        DateContext.get('date').format
      );

      this.$el.html(
        this.template({
          dueDateLabel: __('teamwork_assistant.widget.due_date'),
          dueDate: localizedDueDate,
        })
      );
    },
  });
});
