
/**
 * Product completeness extension
 * Displays the global completeness of the product.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import UserContext from 'pim/user-context'
import template from 'pim/template/product/form/product-completeness'
export default BaseForm.extend({
  template: _.template(template),

  render: function () {
    this.$el.empty()

    var ratio = this.getRatio()
    if (ratio !== null) {
      this.$el.append(this.template({
        label: __('pim_enrich.entity.product.completeness'),
        completeness: ratio,
        badgeClass: this.getBadgeClass()
      }))
    }

    return this
  },

  /**
   * Returns the ratio of the current locale and current scope
   *
   * @returns number|null
   */
  getRatio: function () {
    var completeness = _.findWhere(
      this.getFormData().meta.completenesses, {
        locale: UserContext.get('catalogLocale')
      }
    )

    if (undefined === completeness) {
      return null
    }

    completeness = completeness.channels[UserContext.get('catalogScope')]
    if (undefined === completeness) {
      return null
    }

    return parseInt(completeness.completeness.ratio)
  },

  /**
   * Returns the HTML class for the badge from the completeness ratio
   *
   * @returns string
   */
  getBadgeClass: function () {
    var ratio = this.getRatio()

    if (ratio <= 0) {
      return 'AknBadge--important'
    }

    if (ratio >= 100) {
      return 'AknBadge--enabled'
    }

    return 'AknBadge--warning'
  }
})
