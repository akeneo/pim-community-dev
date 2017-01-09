'use strict';

/**
 * Delete extension for association type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/association-type'], function (DeleteForm, AssociationTypeRemover) {
    return DeleteForm.extend({
        remover: AssociationTypeRemover
    });
});
