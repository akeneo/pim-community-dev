'use strict';

define([
  'underscore',
  'pim/form',
  'oro/translator',
  'react',
  'react-dom',
  'akeneosharedcatalog/job/form/recipients',
], function(_, BaseForm, __, React, ReactDOM, Recipients) {
  return BaseForm.extend({
    className: 'tabbable recipients',
    validationErrors: [],

    initialize: function(meta) {
      this.config = _.extend({}, meta.config);

      return BaseForm.prototype.initialize.call(this, arguments);
    },

    configure: function() {
      this.trigger('tab:register', {
        code: this.config.tabCode ? this.config.tabCode : this.code,
        label: __(this.config.title),
      });

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onValidationError.bind(this));

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    render: function() {
      ReactDOM.unmountComponentAtNode(this.el);
      this.$el.empty();

      const Component = React.createElement(Recipients.default, {
        recipients: this.getFormData().configuration.recipients ? this.getFormData().configuration.recipients : [],
        validationErrors: this.validationErrors,
        onRecipientsChange: recipients => {
          const configuration = {...this.getFormData().configuration, recipients};
          const updatedData = {...this.getFormData(), configuration};

          this.setData(updatedData);
        },
      });

      ReactDOM.render(Component, this.el);

      return this;
    },

    onValidationError: function onValidationError(event) {
      this.validationErrors = event.response.configuration.recipients || [];
      this.render();
    },
  });
});
