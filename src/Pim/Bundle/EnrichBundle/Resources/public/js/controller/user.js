import FormController from 'pim/controller/form'

export default FormController.extend({
  /**
   * {@inheritdoc}
   */
  afterSubmit: function () {
    window.location.reload() // TODO nav: reload the page to update the menu

    FormController.prototype.afterSubmit.apply(this, arguments)
  }
})
