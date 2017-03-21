'use strict';

/**
 * Save extension for association type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
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
        'pim/saver/association-type',
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
        AssociationTypeSaver,
        FieldManager,
        i18n,
        UserContext
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.association_type.info.update_successful'),
            updateFailureMessage: __('pim_enrich.entity.association_type.info.update_failed'),

            /**
             * {@inheritdoc}
             */
            save: function () {
                var associationType = $.extend(true, {}, this.getFormData());

                delete associationType.meta;

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
                        __(
                            'pim_enrich.entity.association_type.info.field_not_ready',
                            {'fields': fieldLabels.join(', ')}
                        )
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return AssociationTypeSaver
                    .save(associationType.code, associationType)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);
