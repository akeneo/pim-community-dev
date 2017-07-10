

/**
 * Save extension for channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import ChannelSaver from 'pim/saver/channel';
import FieldManager from 'pim/field-manager';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import Routing from 'routing';
import router from 'pim/router';
export default BaseSave.extend({
    updateSuccessMessage: __('pim_enrich.entity.channel.info.update_successful'),
    updateFailureMessage: __('pim_enrich.entity.channel.info.update_failed'),
    createSuccessMessage: __('pim_enrich.entity.channel.info.create_successful'),
    createFailureMessage: __('pim_enrich.entity.channel.info.create_failed'),

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

