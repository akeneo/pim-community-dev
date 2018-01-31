'use strict';

/**
 * Delete extension for group type
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/group-type'], function (DeleteForm, GroupTypeRemover) {
    return DeleteForm.extend({
        remover: GroupTypeRemover
    });
});
