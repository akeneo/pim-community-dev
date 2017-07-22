/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

import $ from 'jquery'
import _ from 'underscore'
import BaseForm from 'pim/form'
import fetcherRegistry from 'pim/fetcher-registry'
import AttributeOptionGrid from 'pim/attributeoptionview'
import template from 'pim/template/attribute/tab/choices/options-grid'
export default BaseForm.extend({
  template: _.template(template),
  locales: [],

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseForm.prototype.configure.apply(this, arguments),
      fetcherRegistry.getFetcher('locale').fetchActivated()
        .then(function (locales) {
          this.locales = locales
        }.bind(this))
    )
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template({
      attributeId: this.getFormData().meta.id,
      sortable: !this.getFormData().auto_option_sorting,
      localeCodes: _.pluck(this.locales, 'code')
    }))

    AttributeOptionGrid(this.$('.attribute-option-grid'))

    this.renderExtensions()
  }
})
