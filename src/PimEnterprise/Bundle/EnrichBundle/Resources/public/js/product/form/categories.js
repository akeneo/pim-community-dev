'use strict';
/**
 * Category tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['pim/product-edit-form/categories'],
    function (Categories) {
        return Categories.extend({
            /**
             * {@inheritdoc}
             */
            isVisible: function () {
                return this.getFormData().meta.is_owner;
            }
        });
    }
);
