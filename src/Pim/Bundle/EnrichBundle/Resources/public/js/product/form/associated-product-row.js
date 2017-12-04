/*
 * This module is a custom row for rendering an asset in the datagrid
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery', 'oro/datagrid/product-row'], function($, BaseRow) {
    return BaseRow.extend({

        /**
         * {@inheritdoc}
         */
        // getTemplateOptions() {
        //     const label = this.model.get('code');
        //     const imagePath = this.model.get('image');

        //     return {
        //         useLayerStyle: false,
        //         identifier: '',
        //         label,
        //         imagePath
        //     };
        // },
        getThumbnailImagePath() {
            const image = this.model.get('image');
            if (undefined === image || null === image) {
                return '/media/show/undefined/preview';
            }

            return mediaUrlGenerator.getMediaShowUrl(image, 'thumbnail');
        },
        /**
         * {@inheritdoc}
         */
        getRenderableColumns() {
            return [];
        }
    });
});


// /**
//  * A module that renders a list of products in gallery view
//  *
//  * @author    Tamara Robichet <tamara.robichet@akeneo.com>
//  * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
//  * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
//  */

// define([
//     'jquery',
//     'underscore',
//     'backbone',
//     'oro/translator',
//     'pim/form',
//     'pim/template/product/tab/associated-products-list',
//     'pim/media-url-generator'
// ], function (
//     $,
//     _,
//     Backbone,
//     __,
//     BaseForm,
//     template,
//     mediaUrlGenerator
// ) {
//     return BaseForm.extend({
//         config: {},

//         products: [],

//         tagName: 'div',

//         className: 'AknGrid AknGrid--gallery',

//         rendered: false,

//         events: {
//             'click .AknIconButton--remove': 'removeProductAssociation'
//         },

//         removeProductAssociation(event) {
//             const item = $(event.currentTarget);
//             const identifier = item.data('identifier');
//             this.getRoot().trigger('datagrid:unselectModel:association-product-grid', identifier);
//             $(item).closest('.AknGrid-bodyRow').remove();
//         },

//         configure() {
//             this.listenTo(this.getRoot(), 'datagrid:associations:ready', this.renderList.bind(this));

//             return BaseForm.prototype.configure.apply(this, arguments);
//         },

//         formatCompleteness(completeness) {
//             let type = 'warning';
//             if (100 === completeness) type = 'success';
//             if (0 === completeness) type = 'important';

//             return type;
//         },

//         formatImage(image) {
//             if (undefined === image || null === image) {
//                 return '/media/show/undefined/preview';
//             }

//             return mediaUrlGenerator.getMediaShowUrl(image, 'thumbnail');
//         },

//         formatProducts(products) {
//             return products.map(product => {

//                 product.completenessType = this.formatCompleteness(product.completeness);
//                 product.image = this.formatImage(product.image);

//                 return product;
//             });
//         },

//         /**
//          * {@inheritdoc}
//          */
//         renderList(data, renderTarget) {
//             const products = this.formatProducts(data);

//             this.$el.html(
//                 _.template(template)({ products })
//             );

//             $(renderTarget).html(this.$el);
//             this.products = products;
//         }
//     });
// });
