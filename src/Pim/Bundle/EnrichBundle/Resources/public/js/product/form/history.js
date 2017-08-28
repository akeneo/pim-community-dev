'use strict';
/**
 * History extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/product/history',
        'routing',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'bootstrap-modal'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        Routing,
        mediator,
        FetcherRegistry,
        UserContext,
        i18n
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane history-panel',
            loading: false,
            versions: [],
            actions: {},
            events: {
                'click .expanded .AknGrid-bodyCell': 'toggleVersion'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __('pim_enrich.form.product.panel.history.title')
                });

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.update);
                this.onExtensions('action:register',  this.addAction.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
                    return this;
                }

                if (this.getFormData().meta) {
                    this.getVersions()
                        .then(function (versions) {
                            this.$el.html(
                                this.template({
                                    versions: versions,
                                    expanded: true,
                                    hasAction: this.actions
                                })
                            );

                            this.renderExtensions();

                            if (this.actions) {
                                _.each(this.$el.find('td.actions'), function (element) {
                                    _.each(this.actions, function (action) {
                                        $(element).append(action.clone(true));
                                    }.bind(this));
                                }.bind(this));
                            }

                            this.delegateEvents();
                        }.bind(this));
                }

                return this;
            },

            /**
             * Update the history by fetching it from the backend
             */
            update: function () {
                if (this.getFormData().meta) {
                    FetcherRegistry.getFetcher('product-history').clear(this.getFormData().meta.id);
                }

                this.render();
            },

            /**
             * Get history versions from the backend
             *
             * @return {Promise}
             */
            getVersions: function () {
                return FetcherRegistry.getFetcher('product-history').fetch(
                    this.getFormData().meta.id,
                    { entityId: this.getFormData().meta.id }
                ).then(this.addAttributesLabelToVersions.bind(this));
            },

            /**
             * Add attributes label to all versions
             *
             * @param {Array} versions
             */
            addAttributesLabelToVersions: function (versions) {
                var codes = this.getAttributeCodesInVersions(versions);

                return FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(codes)
                    .then(function (attributes) {
                        _.each(versions, function (version) {
                            _.each(version.changeset, function (data, index) {
                                var code      = index.split('-')[0];
                                var attribute = _.findWhere(attributes, { code: code });
                                data.label    = attribute ? this.getAttributeLabel(attribute, index) : index;
                            }.bind(this));
                        }.bind(this));

                        return versions;
                    }.bind(this));
            },

            /**
             * Return the list of unique attribute codes found in all versions
             *
             * @param {Array} versions
             *
             * @returns {Array}
             */
            getAttributeCodesInVersions: function (versions) {
                var codes = [];
                _.each(versions, function (version) {
                    _.each(version.changeset, function (data, index) {
                        codes.push(index.split('-')[0]);
                    });
                });

                return _.uniq(codes);
            },

            /**
             * Get attribute label
             *
             * @param {object} attribute
             * @param {string} key
             *
             * @return {string}
             */
            getAttributeLabel: function (attribute, key) {
                var uiLocale = UserContext.get('catalogLocale');
                var label    = i18n.getLabel(attribute.labels, uiLocale, attribute.code);

                key = key.split('-');
                key.shift();

                var info = '';
                if (attribute.localizable) {
                    info += i18n.getFlag(key.shift());
                }
                if (attribute.scopable) {
                    info = ' <span>' + key.shift() + '</span>' + info;
                }
                if (0 < key.length) {
                    info = key.join(' ') + info;
                }
                if (info) {
                    info = ' <span class="attribute-info">' + info + '</span>';
                }

                return label + info;
            },

            /**
             * Add action to the history
             *
             * @param {Event} event
             */
            addAction: function (event) {
                this.actions[event.code] = event.element;
            },

            /**
             * Toggle history version line
             *
             * @param {Event} event
             */
            toggleVersion: function (event) {
                var $row = $(event.currentTarget).closest('.AknGrid-bodyRow');
                var $body = $row.closest('.AknGrid');

                $body.find('tr.changeset').addClass('hide');
                $body.find('.AknGrid-expand').removeClass('AknGrid-expand--expanded');

                if (!$row.hasClass('expanded')) {
                    $row.next('tr.changeset').removeClass('hide');
                    $row.find('.AknGrid-expand').addClass('AknGrid-expand--expanded');
                }
                $row.siblings().removeClass('expanded');
                $row.toggleClass('expanded');
            }
        });
    }
);
