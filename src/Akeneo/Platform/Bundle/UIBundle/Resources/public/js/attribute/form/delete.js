/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(['pim/form/common/delete', 'pim/remover/attribute'], function (DeleteForm, AttributeRemover) {
    return DeleteForm.extend({
        remover: AttributeRemover
    });
});
