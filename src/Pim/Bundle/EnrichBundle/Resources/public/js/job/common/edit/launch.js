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
        'pim/router',
        'pim/common/property',
        'oro/messenger',
        'oro/loading-mask',
        'pim/template/export/common/edit/launch'
    ],
    function ($, _, __, BaseForm, Routing, router, propertyAccessor, messenger, LoadingMask, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .AknButton': 'launch'
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
                    this.$el.empty();
                    if (!isVisible) {
                        return this;
                    }

                    this.$el.html(this.template({
                        label: __(this.config.label)
                    }));
                }.bind(this));

                this.delegateEvents();

                return this;
            },

            /**
             * Launch the job
             */
            launch: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                $.post(this.getUrl())
                    .then(function (response) {
                        router.redirect(response.redirectUrl);
                    })
                    .fail(function () {
                        messenger.notify('error', __('pim_import_export.form.job_instance.fail.launch'));
                    })
                    .always(function () {
                        loadingMask.hide().$el.remove();
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
