const BaseIndex = require('pim/controller/common/index');
const FormBuilder = require('pim/form-builder');

class MappingIndex extends BaseIndex {
  /**
   * {@inheritdoc}
   *
   * This is the same method than the parent, but adding the 'can-leave' mechanism.
   */
  public renderForm(): object {
    return FormBuilder.build('pim-' + this.options.config.entity + '-index')
      .then((form: any) => {
        this.on('pim:controller:can-leave', (event: any) => {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setElement(this.$el).render();
        return form;
      });
  }
}

export = MappingIndex;
