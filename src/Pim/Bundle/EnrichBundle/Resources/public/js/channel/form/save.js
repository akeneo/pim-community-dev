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
        'pim/saver/channel',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context',
        'routing',
        'module',
        'oro/navigation'
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
        module,
        Navigation
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.channel.info.update_successful'),
            updateFailureMessage: __('pim_enrich.entity.channel.info.update_failed'),
            createSuccessMessage: __('pim_enrich.entity.channel.info.create_successful'),
            createFailureMessage: __('pim_enrich.entity.channel.info.create_failed'),
            wasNew: false,

            /**
             * {@inheritdoc}
             */
            postSave: function () {
                this.getRoot().trigger('pim_enrich:form:entity:post_save');
                var code = this.getFormData().code;
                if (this.wasNew) {
                    messenger.notificationFlashMessage(
                        'success',
                        this.createSuccessMessage
                    );
                    var navigation = Navigation.getInstance();
                    navigation.setLocation(Routing.generate(module.config().redirectUrl, {'code': code}));
                } else {
                    messenger.notificationFlashMessage(
                        'success',
                        this.updateSuccessMessage
                    );
                }
            },

            /**
             * {@inheritdoc}
             */
            save: function () {
                var channel = $.extend(true, {}, this.getFormData());
                var code = null;

                if (_.has(channel.meta, 'id')) {
                    code = channel.code;
                } else {
                    this.wasNew = true;
                }

                delete channel.meta;

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ChannelSaver
                    .save(code, channel)
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
