'use strict';

/**
 * Family add attribute group select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/common/add-select'
    ],
    function (
        BaseAddSelect
    ) {
        return BaseAddSelect.extend({
            className: 'AknButtonList-item add-attribute-group'
        });
    }
);

