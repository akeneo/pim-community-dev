 'use strict';
/**
 * Displays the created at meta information
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/meta/created',
        'pim/template/product/meta/created'
    ],
    function (_, Created, template) {
        return Created.extend({
            className: 'AknColumn-block',

            template: _.template(template)
        });
    }
);
