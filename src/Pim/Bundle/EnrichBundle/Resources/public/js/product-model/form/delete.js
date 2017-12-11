'use strict';

/**
 * Delete product model extension
 *
 * @author    Florian Klein (florian.klein@akeneo.com)
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/product-model'], function (DeleteForm, ProductModelRemover) {
    return DeleteForm.extend({
        remover: ProductModelRemover,

        /**
         * {@inheritdoc}
         */
        getIdentifier: function () {
            return this.getFormData().meta.id;
        }
    });
});
