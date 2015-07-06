'use strict';
/**
 * Save extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/form',
        'oro/messenger',
        'oro/loading-mask',
        'pim/product-manager',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context'
    ],
    function ($,
            _,
            mediator,
            BaseForm,
            messenger,
            LoadingMask,
            ProductManager,
            FieldManager,
            i18n,
            UserContext
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            updateSuccessMessage: _.__('pim_enrich.entity.product.info.update_successful'),
            updateFailureMessage: _.__('pim_enrich.entity.product.info.update_failed'),
            configure: function () {
                if ('save-buttons' in this.parent.extensions) {
                    this.parent.extensions['save-buttons'].addButton({
                        className: 'save-product',
                        priority: 200,
                        label: _.__('pim_enrich.entity.product.btn.save'),
                        events: {
                            'click .save-product': _.bind(this.save, this)
                        }
                    });
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            save: function (options) {
                _.each(FieldManager.getFields(), function (field) {
                    field.updateModel();
                });

                var product = $.extend(true, {}, this.getData());
                var productId = product.meta.id;

                delete product.variant_group;
                delete product.meta;

                var notReadyFields = FieldManager.getNotReadyFields();

                if (0 < notReadyFields.length) {
                    var fieldLabels = _.map(notReadyFields, function (field) {
                        return i18n.getLabel(
                            field.attribute.label,
                            UserContext.get('catalogLocale'),
                            field.attribute.code
                        );
                    });

                    messenger.notificationFlashMessage(
                        'error',
                        _.__('pim_enrich.entity.product.info.field_not_ready', {'fields': fieldLabels.join(', ')})
                    );

                    return;
                }

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                mediator.trigger('product:action:pre_save');

                return ProductManager
                    .save(productId, product)
                    .done(_.bind(ProductManager.generateMissing, this))
                    .done(_.bind(function (data) {
                        messenger.notificationFlashMessage(
                            'success',
                            this.updateSuccessMessage
                        );

                        this.setData(data);

                        if (!options || !options.silent) {
                            mediator.trigger('product:action:post_update', data);
                        }
                    }, this))
                    .fail(function (response) {
                        switch (response.status) {
                            case 400:
                                mediator.trigger(
                                    'entity:action:validation_error',
                                    {'sentData': product, 'response': response.responseJSON}
                                );
                                break;
                            case 500:
                                /* global console */
                                console.log('Errors:', response.responseJSON);
                                mediator.trigger('entity:error:save', response.responseJSON);
                                break;
                            default:
                        }

                        messenger.notificationFlashMessage(
                            'error',
                            this.updateFailureMessage
                        );
                    }).always(function () {
                        loadingMask.hide().$el.remove();
                    });
            }
        });
    }
);
