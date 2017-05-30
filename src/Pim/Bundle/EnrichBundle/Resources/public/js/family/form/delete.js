'use strict';

/**
 * Family delete extension
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/family'], function (DeleteForm, FamilyRemover) {
    return DeleteForm.extend({
        remover: FamilyRemover
    });
});
