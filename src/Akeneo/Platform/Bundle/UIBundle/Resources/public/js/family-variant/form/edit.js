'use strict';

/**
 * Family variant form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/form/common/edit-form',
        'pim/template/family-variant/edit'
    ],
    function (
        _,
        BaseEdit,
        template
    ) {
        return BaseEdit.extend({
            // TODO This can be replaced by a common/edit-form specifying the template!
            template: _.template(template),
        });
    }
);
