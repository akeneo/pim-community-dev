'use strict';

/**
 * Delete extension for family variants
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/family-variant'], function (DeleteForm, FamilyVariantRemover) {
    return DeleteForm.extend({ remover: FamilyVariantRemover });
});
