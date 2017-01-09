/**
 * Override of generic "add-attributes" module to fit export builder's specific needs.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

'use strict';

define(['jquery', 'pim/common/add-attribute'], function ($, AddAttribute) {
    return AddAttribute.extend({
        /**
         * {@inherit}
         */
        getExcludedAttributes: function () {
            return $.Deferred().resolve(this.getParent().getCurrentFilters());
        }
    });
});
