'use strict';

/**
 * Save extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/save',
        'oro/messenger',
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
        FieldManager,
        i18n,
        UserContext,
        Routing,
        router
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_import_export.entity.job_instance.flash.update.success'),
            updateFailureMessage: __('pim_import_export.entity.job_instance.flash.update.fail'),

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
    }
);
