/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseField from 'pim/attribute-edit-form/properties/field'
import fetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import template from 'pim/template/attribute/tab/properties/group'

export default BaseField.extend({
  template: _.template(template),
  attributeGroups: {},

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseField.prototype.configure.apply(this, arguments),
      fetcherRegistry.getFetcher('attribute-group').fetchAll()
        .then(function (attributeGroups) {
          this.attributeGroups = attributeGroups
        }.bind(this))
    )
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(_.extend(templateContext, {
      value: this.getFormData()[this.fieldName],
      groups: _.sortBy(this.attributeGroups, 'sort_order'),
      i18n: i18n,
      locale: UserContext.get('catalogLocale'),
      labels: {
        defaultLabel: __('pim_enrich.form.attribute.tab.properties.default_label.group')
      }
    }))
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('select.select2').select2()
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val()
  }
})
