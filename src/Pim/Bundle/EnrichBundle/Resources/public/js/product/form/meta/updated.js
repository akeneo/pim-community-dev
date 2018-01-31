 'use strict';
/**
 * Displays the updated at meta information
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/meta/updated',
        'pim/template/product/meta/updated'
    ],
    function (_, Updated, template) {
        return Updated.extend({
            className: 'AknColumn-block',

            template: _.template(template)
        });
    }
);
