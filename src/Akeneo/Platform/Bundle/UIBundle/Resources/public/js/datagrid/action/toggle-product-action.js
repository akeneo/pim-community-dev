define(['oro/datagrid/ajax-action'], function (AjaxAction) {
  return AjaxAction.extend({
    /** @property {Boolean} */
    noHref: true,

    /**
     * {@inheritdoc}
     */
    initialize() {
      this.launcherOptions.enabled = this.isEnabled();

      AjaxAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    isEnabled() {
      return this.model.get('document_type') !== 'product_model';
    },

    /**
     * {@inheritdoc}
     */
    getMethod: function () {
      return 'POST';
    },
  });
});
