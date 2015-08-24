'use strict';
/**
 * Associations tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['pim/product-edit-form/associations'],
    function (Associations) {
        return Associations.extend({
            /**
             * {@inheritdoc}
             */
            isVisible: function () {
                return this.getFormData().meta.is_owner;
            }
        });
    }
);
