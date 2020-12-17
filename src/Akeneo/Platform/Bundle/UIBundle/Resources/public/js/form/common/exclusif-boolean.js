'use strict';

define(['underscore', 'pim/form', 'pim/template/form/exclusif-boolean'], function (_, {BaseForm}, template) {
  return BaseForm.extend({
    template: _.template(template),

    /**
     * {@inheritdoc}
     */
    configure: function () {
      let self = this;
      this.booleanExtensions = Object.values(this.extensions).filter(extension => {
        return self.isBooleanExtension(extension);
      });

      BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      this.$el.html(
        this.template({
          fields: null,
        })
      );

      let extensionChecked = this.getCheckedExtension();
      Object.values(this.booleanExtensions).forEach(function (extension) {
        extension.readOnly = extensionChecked && extensionChecked !== extension;
      });

      this.renderExtensions();
    },

    isBooleanExtension: function (extension) {
      return extension.options.module === 'pim/form/common/fields/boolean';
    },

    getCheckedExtension: function () {
      let formData = this.getFormData();
      let checkedExtension = Object.values(this.booleanExtensions).filter(extension => {
        return formData.hasOwnProperty(extension.fieldName) && formData[extension.fieldName] === true;
      });

      return checkedExtension[0] || null;
    },
  });
});
