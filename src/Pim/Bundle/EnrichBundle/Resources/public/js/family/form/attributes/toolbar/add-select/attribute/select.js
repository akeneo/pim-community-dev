
/**
 * Family edit form add attribute select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import AddAttributeSelect from 'pim/product/add-select/attribute'
export default AddAttributeSelect.extend({
            /**
             * {@inheritdoc}
             */
  getItemsToExclude: function () {
    return $.Deferred().resolve(
                    _.pluck(
                        this.getFormData().attributes,
                        'code'
                    )
                )
  },

            /**
             * {@inheritdoc}
             */
  addItems: function () {
    this.getRoot().trigger(this.addEvent, { codes: this.selection })
  },

            /**
             * {@inheritdoc}
             */
  getSelectSearchParameters: function () {
    return _.extend({}, AddAttributeSelect.prototype.getSelectSearchParameters.apply(this, arguments), {
      rights: 0
    })
  }
})
