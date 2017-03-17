'use strict';

/**
 * Save extension for simple entity types
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
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
        'pim/saver/entity-saver',
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
        EntitySaver,
        FieldManager,
        i18n,
        UserContext
    ) {
        return BaseSave.extend({

            /**
             * Sets message labels for updates
             */
            configure: function () {
                this.updateSuccessMessage = __(this.config.updateSuccessMessage);
                this.updateFailureMessage = __(this.config.updateFailureMessage);
                this.notReadyMessage = __(this.config.notReadyMessage);
                return BaseSave.prototype.configure.apply(this, arguments);
            },

            /**
             * Given an array of fields, return the translation for each in a map
             *
             * @param  {Array} fields         An array of field objects
             * @param  {String} catalogLocale The locale
             * @return {Array}                An array of labels
             */
            getFieldLabels: function (fields, catalogLocale) {
                return _.map(fields, function (field) {
                    return i18n.getLabel(
                        field.attribute.label,
                        catalogLocale,
                        field.attribute.code
                    );
                });
            },

            /**
             * Shows an error message for the given message text and labels
             *
             * @param  {String} message The given error message
             * @param  {Array} labels   An array of field names
             */
            showFlashMessage: function (message, labels) {
                var flash = __(message, { 'fields': labels.join(', ') });
                messenger.notificationFlashMessage('error', flash);
            },

            /**
             * {@inheritdoc}
             */
            save: function () {
                var entity = $.extend(true, {}, this.getFormData());
                delete entity.meta;

                var notReadyFields = FieldManager.getNotReadyFields();

                if (0 < notReadyFields.length) {
                    var catalogLocale = UserContext.get('catalogLocale');
                    var fieldLabels = this.getFieldLabels(notReadyFields, catalogLocale);
                    return this.showFlashMessage(this.notReadyMessage, fieldLabels);
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return EntitySaver
                    .setUrl(this.config.url)
                    .save(entity.code, entity)
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
