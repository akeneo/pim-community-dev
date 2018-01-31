'use strict';

/**
 * Prodct add attribute select line view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/common/add-select/line',
        'pim/template/product/add-select/attribute/line'
    ],
    function (
        $,
        _,
        BaseLine,
        template
    ) {
        return BaseLine.extend({
            template: _.template(template)
        });
    }
);
