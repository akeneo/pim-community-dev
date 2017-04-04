'use strict';

/**
 * Root view component for family mass edit action
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/edit-form',
        'text!pim/template/family-mass-edit/base-form'
    ],
    function (
        _,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template)
        });
    }
);
