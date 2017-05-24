'use strict';

/**
 * Delete extension for attribute group
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/attribute-group'], function (DeleteForm, AttributeGroupRemover) {
    return DeleteForm.extend({
        remover: AttributeGroupRemover
    });
});
