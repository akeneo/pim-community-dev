'use strict';
/**
 * Status switcher extension override to take ownership of product in account
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/product-edit-form/status-switcher'
    ],
    function (
        StatusSwitcher
    ) {
        return StatusSwitcher.extend({
            render: function () {
                if (!this.getRoot().getFormData().meta.is_owner) {
                    return this.remove();
                }

                return StatusSwitcher.prototype.render.apply(this, arguments);
            }
        });
    }
);
