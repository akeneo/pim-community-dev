'use strict';

define([
  'underscore',
  'pim/form',
  'oro/translator',
  'react',
  'react-dom',
  'akeneosharedcatalog/job/form/Branding',
], function(_, BaseForm, __, React, ReactDOM, {Branding}) {
  return BaseForm.extend({
    className: 'tabbable branding',
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

      const Component = React.createElement(Branding, {
        branding: this.getFormData().configuration.branding || {image: null},
        validationErrors: this.validationErrors,
        onBrandingChange: branding => {
          const data = {...this.getFormData()};
          const configuration = {...data.configuration, branding};
          const updatedData = {...data, configuration};

          this.setData(updatedData);
        },
      });

      ReactDOM.render(Component, this.el);

      return this;
    },

    onValidationError: function(event) {
      const brandingErrors = event.response.configuration.branding || [];
      this.validationErrors = Array.isArray(brandingErrors) ? brandingErrors : [brandingErrors];
      this.render();
    },
  });
});
