'use strict';

/**
 * User delete extension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/user'], function (DeleteForm, UserRemover) {
    return DeleteForm.extend({
        remover: UserRemover,

        /**
         * {@inheritdoc}
         */
        getIdentifier: function () {
            return this.getFormData().meta.id;
        }
    });
});

