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
        'pimee/template/product/unpublish',
        'routing',
        'pimee/product-edit-form/publish',
        'pim/router'
    ],
    function (
        _,
        template,
        Routing,
        Publish,
        router
    ) {
        return Publish.extend({
            className: 'AknButtonList-item',
            template: _.template(template),
            getProductId: function () {
                return this.getFormData().meta.original_product_id;
            },
            togglePublished: function () {
                Publish.prototype.togglePublished.apply(this, arguments).then(function () {
                    router.redirectToRoute('pimee_workflow_published_product_index');
                });
            }
        });
    }
);
