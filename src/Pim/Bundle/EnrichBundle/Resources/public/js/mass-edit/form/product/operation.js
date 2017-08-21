'use strict';
/**
 * Base operation
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
        'pim/form/common/edit-form'
    ],
    function (
        $,
        _,
        __,
        BaseForm
    ) {
        return BaseForm.extend({
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Called to reset the operation module
             */
            reset: function () {},

            /**
             * The label diplayed in the operation list
             *
             * @return {string}
             */
            getLabel: function () {
                return __(this.config.label);
            },

            /**
             * [getDescription description]
             * @return {[type]} [description]
             */
            getDescription: function () {
                return __(this.config.description);
            },

            /**
             * Get the operation code
             *
             * @return {string}
             */
            getCode: function () {
                return this.config.code;
            },

            /**
             * Get the operation icon
             *
             * @return {string}
             */
            getIcon: function () {
                return this.config.icon;
            },

            /**
             * Get job instance code to launch
             *
             * @return {string}
             */
            getJobInstanceCode: function () {
                return this.config.jobInstanceCode;
            },

            /**
             * Called when the operation should switch from read only or edit
             *
             * @param {boolean} readOnly
             */
            setReadOnly: function (readOnly) {
                this.readOnly = readOnly;
            },

            /**
             * Called before the confirmation step to validate the model
             *
             * @return {promise}
             */
            validate: function () {
                return $.Deferred().resolve(true);
            }
        });
    }
);
