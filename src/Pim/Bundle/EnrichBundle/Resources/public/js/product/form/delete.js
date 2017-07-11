

/**
 * Delete product extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import ProductRemover from 'pim/remover/product'
export default DeleteForm.extend({
    remover: ProductRemover,

        /**
         * {@inheritdoc}
         */
    getIdentifier: function () {
        return this.getFormData().meta.id
    }
})

