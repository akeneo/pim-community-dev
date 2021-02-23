'use strict';
/**
 * Category tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['underscore', 'pim/product-edit-form/categories'],
    function (_, Categories) {
        return Categories.extend({
            /**
             * {@inheritdoc}
             */
            isReadOnly: function () {
                return !_.result(
                    _.result(this.getFormData(), 'meta', {}),
                    'is_owner',
                    false
                );
            }
        });
    }
);
