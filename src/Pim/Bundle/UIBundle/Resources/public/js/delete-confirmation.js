import _ from 'underscore'
import __ from 'oro/translator'
import Modal from 'oro/modal'

    /**
     * Delete confirmation dialog
     *
     * @export  oro/delete-confirmation
     * @class   oro.DeleteConfirmation
     * @extends oro.Modal
     */
export default Modal.extend({
        /**
         * @param {Object} options
         */
  initialize: function (options) {
    options = _.extend({
      title: __('Delete Confirmation'),
      okText: __('Yes, Delete'),
      cancelText: __('Cancel')
    }, options)

    arguments[0] = options
    Modal.prototype.initialize.apply(this, arguments)
  }
})
