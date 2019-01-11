'use strict';
/**
 * completeness filter extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'underscore',
  'pim/form',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/provider/to-fill-field-provider',
], function($, _, BaseForm, fetcherRegistry, UserContext, toFillFieldProvider) {
  return BaseForm.extend({
    configure: function() {
      this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritDoc}
     */
    addFieldExtension: function(event) {
      const scope = UserContext.get('catalogScope');
      const locale = UserContext.get('catalogLocale');
      const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(this.getFormData(), scope, locale);
      const field = event.field;

      if (_.contains(fieldsToFill, field.attribute.code)) {
        field.addElement('badge', 'completeness', '<span class="AknBadge AknBadge--small AknBadge--highlight"></span>');
      }
    },
  });
});
