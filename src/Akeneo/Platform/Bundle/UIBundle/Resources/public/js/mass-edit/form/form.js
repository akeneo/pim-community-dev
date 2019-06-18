'use strict';
/**
 * Mass edit root form
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
        'pim/router',
        'routing',
        'oro/messenger',
        'pim/form/common/edit-form',
        'oro/loading-mask',
        'pim/template/mass-edit/form'
    ],
    function (
        $,
        _,
        __,
        router,
        Routing,
        messenger,
        BaseForm,
        LoadingMask,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            currentStep: 'choose',
            events: {
                'click .wizard-action': function (event) {
                    this.applyAction(event.target.dataset.actionTarget);
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'mass-edit:navigate:action',
                    this.applyAction.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var step = this.currentStep === 'choose' ?
                    this.getChooseExtension() :
                    this.getOperationExtension(this.getCurrentOperation());

                var currentStepNumber;
                switch (this.currentStep) {
                    case 'configure':
                        currentStepNumber = 1;
                        break;
                    case 'confirm':
                        currentStepNumber = 2;
                        break;
                    default:
                        currentStepNumber = 0;
                        break;
                }

                const itemsCount = this.getFormData().itemsCount;
                this.$el.html(this.template({
                    currentStep: this.currentStep,
                    currentStepNumber: currentStepNumber,
                    currentOperation: this.getCurrentOperation(),
                    label: step.getLabel(),
                    description: step.getDescription(),
                    title: step.getTitle(),
                    labelCount: step.getLabelCount(),
                    confirm: __(this.config.confirm, {itemsCount}, itemsCount),
                    previousLabel: __('pim_common.previous'),
                    nextLabel: __('pim_common.next'),
                    confirmLabel: __('pim_common.confirm'),
                    illustrationClass: step.getIllustrationClass(),
                    __: __
                }));

                this.$('.step').empty().append(step.$el);
                // We need to have the step in the DOM as soon as possible for extensions that call render() and
                // postRender()
                step.render();

                this.delegateEvents();
            },

            /**
             * Provide the list of operations available
             *
             * @return {array}
             */
            getOperations: function () {
                return _.chain(this.extensions)
                    .filter(function (extension) {
                        return extension.options.config.label !== undefined;
                    }).map(function (extension) {
                        return {
                            code: extension.getCode(),
                            label: extension.getLabel(),
                            icon: extension.getIcon()
                        };
                    }).value();
            },

            /**
             * Get the chose extension
             *
             * @return {object}
             */
            getChooseExtension: function () {
                return _.filter(this.extensions, function (extension) {
                    return extension.targetZone === 'choose';
                })[0];
            },

            /**
             * The the porvided extension as the current one
             *
             * @param {object} operation
             */
            setCurrentOperation: function (operation) {
                var data = this.getFormData();

                data.operation = operation;
                data.jobInstanceCode = this.getOperationExtension(operation).getJobInstanceCode();

                this.setData(data);

                this.render();
            },

            /**
             * Provide the current oparation
             *
             * @return {string}
             */
            getCurrentOperation: function () {
                return this.getFormData().operation;
            },

            /**
             * Get the operation module corresponding to the given parameter
             *
             * @param {string} operationCode
             *
             * @return {object}
             */
            getOperationExtension: function (operationCode) {
                return _.find(this.extensions, (extension) => {
                    return typeof extension.getCode === 'function' && extension.getCode() === operationCode;
                });
            },

            /**
             * Apply the action triggered by a dom event
             *
             * @param {String} action
             */
            applyAction: function (action) {
                switch (action) {
                    case 'grid':
                        router.redirectToRoute(this.config.backRoute);
                        break;
                    case 'choose':
                        this.currentStep = 'choose';
                        this.render();
                        break;
                    case 'configure':
                        var operationView = this.getOperationExtension(this.getCurrentOperation());
                        if ('choose' === this.currentStep) {
                            operationView.reset();
                        }

                        this.currentStep = 'configure';

                        operationView.setReadOnly(false);
                        this.render();
                        break;
                    case 'confirm':
                        var operationView = this.getOperationExtension(this.getCurrentOperation());

                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                        operationView.validate().then((isValid) => {
                            if (isValid) {
                                operationView.setReadOnly(true);
                                this.currentStep = 'confirm';
                                this.render();
                                this.getRoot().trigger('mass-edit:action:confirm');
                            }
                        })
                        .always(() => {
                            loadingMask.hide().$el.remove();
                        });
                        break;
                    case 'validate':
                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                        $.ajax({
                            method: 'POST',
                            contentType: 'application/json',
                            url: Routing.generate('pim_enrich_mass_edit_rest_launch'),
                            data: JSON.stringify(this.getFormData())
                        }).then(() => {
                            router.redirectToRoute(this.config.backRoute);

                            messenger.notify(
                                'success',
                                __(
                                    this.config.launchedLabel,
                                    {
                                        operation: this.getOperationExtension(this.getCurrentOperation()).getLabel()
                                    }
                                )
                            );
                        })
                        .fail(() => {
                            messenger.notify(
                                'error', __(this.config.launchErrorLabel)
                            );
                        })
                        .always(() => {
                            loadingMask.hide().$el.remove();
                        });

                        break;
                }
            },

            /**
             * Disables the next button when the next step can not be accessed.
             */
            disableNextButton: function() {
                this.$el.find('.next').addClass('AknButton--disabled').removeClass('wizard-action');
            }
        });
    }
);
