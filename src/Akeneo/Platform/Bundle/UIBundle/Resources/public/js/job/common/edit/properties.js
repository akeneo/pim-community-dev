'use strict';
/**
 * Properties form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'oro/translator',
  'pim/template/export/common/edit/properties',
  'pim/common/tab',
  'pim/common/property',
  'pim/edition',
], function (_, __, template, BaseTab, propertyAccessor, pimEdition) {
  return BaseTab.extend({
    template: _.template(template),
    errors: {},

    /**
     * {@inherit}
     */
    configure: function () {
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.resetValidationErrors.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.setValidationErrors.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render.bind(this));

      return BaseTab.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inherit}
     */
    registerTab: function () {
      this.trigger('tab:register', {
        code: this.config.tabCode ? this.config.tabCode : this.code,
        label: __(this.config.tabTitle),
        isVisible: () => !(this.config.hideForCloudEdition && pimEdition.isCloudEdition()),
      });
    },

    /**
     * Set validation errors after save request failure
     *
     * @param {event} event
     */
    setValidationErrors: function (event) {
      this.errors = event.response;
    },

    /**
     * Remove validation error
     */
    resetValidationErrors: function () {
      if (Object.entries(this.errors).length >= 0) {
        this.getRoot().trigger(
          'pim_enrich:form:form-tabs:remove-error',
          this.config.tabCode ? this.config.tabCode : this.code
        );
        this.errors = {};
      }
    },

    /**
     * Get the validtion errors for the given field
     *
     * @param {string} field
     *
     * @return {mixed}
     */
    getValidationErrorsForField: function (field) {
      return propertyAccessor.accessProperty(this.errors, field, null);
    },

    /**
     * {@inherit}
     */
    render: function () {
      if (!this.configured) {
        return this;
      }

      this.$el.html(this.template({__: __}));

      this.renderExtensions();
    },
  });
});
