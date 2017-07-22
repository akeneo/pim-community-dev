/**
 * Identifier field to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import FieldForm from 'pim/form/common/creation/field'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import __ from 'oro/translator'
import FetcherRegistry from 'pim/fetcher-registry'
import errorTemplate from 'pim/template/product-create-error'

export default FieldForm.extend({
  errorTemplate: _.template(errorTemplate),

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render: function () {
    return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
      .then(function (identifier) {
        this.$el.html(this.template({
          identifier: this.identifier,
          label: i18n.getLabel(identifier.labels, UserContext.get('catalogLocale'), identifier.code),
          requiredLabel: __('pim_enrich.form.required'),
          errors: this.getRoot().validationErrors,
          value: this.getFormData()[this.identifier]
        }))

        this.delegateEvents()

        return this
      }.bind(this)).fail(() => {
        this.$el.html(this.errorTemplate({
          message: __('error.creating.product')
        }))
      })
  }
})
