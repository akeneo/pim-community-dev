'use strict';

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/save',
        'oro/messenger',
        'pim/saver/product-model',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        ProductModelSaver,
        FieldManager,
        i18n,
        UserContext
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.product_model.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.product_model.flash.update.fail'),

            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

                return BaseSave.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            save: function (options) {
                var productModel = $.extend(true, {}, this.getFormData());
                var productModelId = productModel.meta.id;

                delete productModel.meta;
                delete productModel.family;

                var notReadyFields = FieldManager.getNotReadyFields();

                if (0 < notReadyFields.length) {
                    var fieldLabels = _.map(notReadyFields, function (field) {
                        return i18n.getLabel(
                            field.attribute.label,
                            UserContext.get('catalogLocale'),
                            field.attribute.code
                        );
                    });

                    messenger.notify(
                        'error',
                        __('pim_enrich.entity.product_model.flash.update.fields_not_ready', {
                            'fields': fieldLabels.join(', ')
                        })
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ProductModelSaver
                    .save(productModelId, productModel)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data, options);

                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);
