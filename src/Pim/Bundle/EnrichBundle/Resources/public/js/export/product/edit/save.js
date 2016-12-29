'use strict';

/**
 * Save extension for Group
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
        'pim/saver/job-instance',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context',
        'routing',
        'oro/navigation'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        JobInstanceSaver,
        FieldManager,
        i18n,
        UserContext,
        Routing,
        Navigation
    ) {
        return BaseSave.extend({
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

                return JobInstanceSaver
                    .save(jobInstance.code, jobInstance)
                    .then(function (data) {
                        Navigation.getInstance().setLocation(
                            Routing.generate(
                                'pim_importexport_import_profile_show',
                                {code: jobInstance.code}
                            )
                        );
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
