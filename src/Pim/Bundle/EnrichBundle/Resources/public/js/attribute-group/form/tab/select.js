
/**
 * Attribute group edit form add attribute select extension view
 *
 * @author   Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

import _ from 'underscore'
import AddAttributeSelect from 'pim/product/add-select/attribute'
import FetcherRegistry from 'pim/fetcher-registry'
import ChoicesFormatter from 'pim/formatter/choices/base'
import LineView from 'pim/common/add-select/line'

export default AddAttributeSelect.extend({
  lineView: LineView,

    /**
     * Render this extension
     *
     * @return {Object}
     */
  render: function () {
    if (!this.hasRightToAdd()) {
      return this
    }

    return AddAttributeSelect.prototype.render.apply(this, arguments)
  },

    /**
     * Creates request according to recieved options
     *
     * @param {Object} options
     */
  onGetQuery: function (options) {
    return FetcherRegistry.getFetcher('attribute').search({
      identifiers: this.getParent().getOtherAttributes().join(','),
      search: options.term
    }).then(this.prepareChoices)
                    .then(function (choices) {
                      options.callback({
                        results: choices,
                        more: false
                      })
                    })
  },

    /**
     * {@inheritdoc}
     */
  prepareChoices: function (items) {
    return _.chain(items).map(function (item) {
      var choice = ChoicesFormatter.formatOne(item)

      return choice
    }).value()
  },

    /**
     * Does the user has right to add an attribute
     *
     * @return {Boolean}
     */
  hasRightToAdd: function () {
    return this.getParent().hasRightToAdd()
  }
})
