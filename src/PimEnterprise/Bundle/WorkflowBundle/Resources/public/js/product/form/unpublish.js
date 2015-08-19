'use strict';
/**
 * Unpublish a product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'text!pimee/template/product/unpublish',
        'routing',
        'pimee/product-edit-form/publish',
        'oro/navigation'
    ],
    function (
        _,
        template,
        Routing,
        Publish,
        Navigation
    ) {
        return Publish.extend({
            className: 'btn-group',
            template: _.template(template),
            getProductId: function () {
                return this.getFormData().meta.original_product_id;
            },
            togglePublished: function () {
                Publish.prototype.togglePublished.apply(this, arguments).then(function () {
                    Navigation.getInstance().setLocation(
                        Routing.generate(
                            'pimee_workflow_published_product_index'
                        )
                    );
                });
            }
        });
    }
);
