'use strict';
/**
 * Delete product extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/delete',
        'oro/navigation',
        'oro/loading-mask',
        'pim/product-manager',
        'routing',
        'pim/dialog'
    ],
    function (
        _,
        mediator,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        ProductManager,
        Routing,
        Dialog
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
            events: {
                'click .delete-product': 'delete'
            },
            render: function () {
                this.$el.html(this.template());
                this.delegateEvents();

                return this;
            },
            delete: function () {
                Dialog.confirm(
                    _.__('confirmation.remove.product'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    this.doDelete.bind(this)
                );
            },
            doDelete: function () {
                var productId   = this.getFormData().meta.id;
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                var navigation = Navigation.getInstance();

                ProductManager.remove(productId)
                    .done(function () {
                        navigation.addFlashMessage(
                            'success',
                            _.__('pim_enrich.entity.product.info.deletion_successful')
                        );
                        navigation.setLocation(Routing.generate('pim_enrich_product_index'));
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            _.__('pim_enrich.entity.product.info.deletion_failed');
                        navigation.addFlashMessage('error', message);
                        navigation.afterRequest();
                    })
                    .always(function () {
                        loadingMask.hide().$el.remove();
                    });
            }
        });
    }
);
