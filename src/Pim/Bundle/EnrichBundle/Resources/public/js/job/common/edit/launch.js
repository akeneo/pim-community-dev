'use strict';
/**
 * Launch button
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
        'pim/form',
        'routing',
        'oro/navigation',
        'pim/common/property',
        'oro/messenger',
        'text!pim/template/export/common/edit/launch'
    ],
    function ($, _, __, BaseForm, Routing, Navigation, propertyAccessor, messenger, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click': 'launch'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.isVisible().then(function (isVisible) {
                    if (!isVisible) {
                        return this;
                    }

                    this.$el.html(this.template({
                        label: __(this.config.label)
                    }));
                }.bind(this));

                return this;
            },

            /**
             * Launch the job
             */
            launch: function () {
                $.post(this.getUrl())
                    .then(function (response) {
                        Navigation.getInstance().setLocation(response.redirectUrl);
                    })
                    .fail(function () {
                        messenger.notificationFlashMessage('error', __('pim_enrich.form.job_instance.fail.launch'));
                    });
            },

            /**
             * Get the route to launch the job
             *
             * @return {string}
             */
            getUrl: function () {
                var params = {};
                params[this.config.identifier.name] = propertyAccessor.accessProperty(
                    this.getFormData(),
                    this.config.identifier.path
                );

                return Routing.generate(this.config.route, params);
            },

            /**
             * Should this extension render
             *
             * @return {Promise}
             */
            isVisible: function () {
                return $.Deferred().resolve(true).promise();
            }
        });
    }
);
