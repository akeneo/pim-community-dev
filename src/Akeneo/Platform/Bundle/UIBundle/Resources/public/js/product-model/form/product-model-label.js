'use strict';
/**
 * Product model label extension
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form/common/label', 'pim/user-context'],
    function (Label, UserContext) {
        return Label.extend({
            /**
             * Provide the object label
             * @return {String}
             */
            getLabel: function () {
                var meta = this.getFormData().meta;

                if (meta && meta.label) {
                    return meta.label[UserContext.get('catalogLocale')];
                }

                return this.getFormData().identifier;
            }
        });
    }
);
