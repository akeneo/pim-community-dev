

/**
 * Save extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import FieldManager from 'pim/field-manager';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import Routing from 'routing';
import router from 'pim/router';
export default BaseSave.extend({
    updateSuccessMessage: __('pim_enrich.entity.job_instance.info.update_successful'),
    updateFailureMessage: __('pim_enrich.entity.job_instance.info.update_failed'),

            /**
             * {@inheritdoc}
             */
    save: function () {
        var jobInstance = $.extend(true, {}, this.getFormData());

        delete jobInstance.meta;
        delete jobInstance.connector;

        this.showLoadingMask();
        this.getRoot().trigger('pim_enrich:form:entity:pre_save');

        return this.getJobInstanceSaver()
                    .save(jobInstance.code, jobInstance)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                        router.redirectToRoute(
                            this.config.redirectPath,
                            {code: jobInstance.code}
                        );
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
    }
});

