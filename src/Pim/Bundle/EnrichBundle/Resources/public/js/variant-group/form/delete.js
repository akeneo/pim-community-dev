'use strict';

/**
 * Delete extension for variant groups
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/variant-group'], function (DeleteForm, VariantGroupRemover) {
    return DeleteForm.extend({
        remover: VariantGroupRemover
    });
});
