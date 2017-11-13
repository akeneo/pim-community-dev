'use strict';

/**
 * Family select2 to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'routing',
    'pim/form/common/fields/simple-select-async'
], function(
    Routing,
    SimpleSelectAsync
) {

    return SimpleSelectAsync.extend({
        /**
         * {@inheritdoc}
         */
        initialize() {
            SimpleSelectAsync.prototype.initialize.apply(this, arguments);

            this.setChoiceUrl(Routing.generate(this.config.choiceUrl));
        }
    });
});
