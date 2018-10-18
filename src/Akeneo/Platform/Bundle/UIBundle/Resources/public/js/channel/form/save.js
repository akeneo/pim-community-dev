'use strict';

/**
 * Save extension for channel
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
        'pim/saver/channel',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context',
        'routing',
        'pim/router'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        ChannelSaver,
        FieldManager,
        i18n,
        UserContext,
        Routing,
        router
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.channel.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.channel.flash.update.fail'),
            createSuccessMessage: __('pim_enrich.entity.channel.flash.create.success'),
            createFailureMessage: __('pim_enrich.entity.channel.flash.create.fail'),

            /**
             * {@inheritdoc}
             */
            postSave: function (isUpdate) {
                this.getRoot().trigger('pim_enrich:form:entity:post_save');
                var code = this.getFormData().code;
                if (!isUpdate) {
                    messenger.notify(
                        'success',
                        this.createSuccessMessage
                    );
                    router.redirectToRoute(this.config.redirectUrl, {'code': code});

                    return;
                }

                messenger.notify(
                    'success',
                    this.updateSuccessMessage
                );
            },

            /**
             * {@inheritdoc}
             */
            save: function () {
                var channel = $.extend(true, {}, this.getFormData());
                var code = null;
                var isUpdate = false;
                var method = 'POST';

                if (_.has(channel.meta, 'id')) {
                    code = channel.code;
                    isUpdate = true;
                    method = 'PUT';
                }

                delete channel.meta;

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ChannelSaver
                    .save(code, channel, method)
                    .then(function (data) {

                        this.setData(data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                        this.postSave(isUpdate);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);
