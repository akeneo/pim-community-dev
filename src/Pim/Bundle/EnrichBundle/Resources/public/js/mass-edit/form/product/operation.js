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
        'underscore',
        'oro/translator',
        'pim/form/common/edit-form',
    ],
    function (
        _,
        __,
        BaseForm
    ) {
        return BaseForm.extend({
            readOnly: false,

            initialize: function (meta) {
                this.config = _.extend({}, meta.config);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            reset: function () {},

            getLabel: function () {
                return __(this.config.label);
            },

            getDescription: function () {
                return __(this.config.description);
            },

            getCode: function () {
                return this.config.code;
            },

            getJobInstanceCode: function () {
                return this.config.jobInstanceCode;
            },

            setReadOnly: function (readOnly) {
                this.readOnly = readOnly;
            },

            validate: function () {
                return $.Deferred().resolve(true);
            }
        });
    }
);
