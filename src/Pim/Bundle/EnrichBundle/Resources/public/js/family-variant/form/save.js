'use strict';

/**
 * Family variant save extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
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
        'pim/saver/family-variant',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context',
        'oro/mediator'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        FamilyVariantSaver,
        FieldManager,
        i18n,
        UserContext,
        mediator
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.family_variant.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.family_variant.flash.update.fail'),

            /**
             * {@inheritdoc}
             */
            save: function () {
                var familyVariant = $.extend(true, {}, this.getFormData());

                delete familyVariant.meta;

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
                        __(
                            'pim_enrich.entity.family_variant.info.field_not_ready',
                            {'fields': fieldLabels.join(', ')}
                        )
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return FamilyVariantSaver
                    .save(familyVariant.code, familyVariant, 'PUT')
                    .then(function (data) {
                        this.postSave();

                        this.setData(data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_save', data);
                        mediator.trigger('datagrid:doRefresh:family-variant-grid');
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);
