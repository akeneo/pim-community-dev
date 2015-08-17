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
        'backbone',
        'pim/form',
        'text!pim/template/product/panel/history',
        'routing',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'backbone/bootstrap-modal'
    ],
    function ($, _, Backbone, BaseForm, template, Routing, mediator, FetcherRegistry, UserContext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane history-panel',
            loading: false,
            versions: [],
            actions: {},
            events: {
                'click .expand-history':   'expandHistory',
                'click .collapse-history': 'collapseHistory',
                'click .expanded>tbody>tr:not(.changeset)': 'toggleVersion'
            },
            configure: function () {
                this.trigger('panel:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.panel.history.title')
                });

                this.listenTo(mediator, 'pim_enrich:form:entity:post_fetch', this.refreshHistory);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.code !== this.getParent().state.get('currentPanel')) {
                    return this;
                }

                if (0 === this.versions.length) {
                    this.refreshHistory();

                    return this;
                }

                if (this.getFormData().meta) {
                    this.$el.html(
                        this.template({
                            versions: this.versions,
                            expanded: this.getParent().getParent().state.get('fullPanel'),
                            hasAction: this.actions
                        })
                    );

                    if (this.getParent().getParent().state.get('fullPanel') && this.actions) {
                        _.each(this.$el.find('td.actions'), function (element) {
                            _.each(this.actions, function (action) {
                                $(element).append(action.clone(true));
                            }.bind(this));
                        }.bind(this));
                    }

                    this.delegateEvents();
                    this.renderExtensions();
                    this.getParent().resize();
                }

                return this;
            },
            refreshHistory: function () {
                if (this.loading) {
                    return;
                }

                this.loading = true;
                if (this.getFormData().meta) {
                    $.getJSON(
                        Routing.generate(
                            'pim_enrich_product_history_rest_get',
                            {
                                entityId: this.getFormData().meta.id
                            }
                        )
                    ).done(function (versions) {
                        this.prepareVersions(versions).done(function (versions) {
                            this.versions = versions;
                            this.render();
                            this.loading = false;
                        }.bind(this));
                    }.bind(this));
                }
            },
            prepareVersions: function (versions) {
                return FetcherRegistry.getFetcher('attribute').fetchAll().then(function (attributes) {
                    _.each(versions, function (version) {
                        _.each(version.changeset, function (data, index) {
                            var code = index.split('-').shift();
                            var attribute = _.findWhere(attributes, { code: code });
                            data.label = attribute ? this.getAttributeLabel(attribute, index) : index;
                        }.bind(this));
                    }.bind(this));

                    return versions;
                }.bind(this));
            },
            getAttributeLabel: function (attribute, key) {
                var uiLocale = UserContext.get('catalogLocale');
                var label    = i18n.getLabel(attribute.label, uiLocale, attribute.code);

                key = key.split('-');
                key.shift();

                var info = '';
                if (attribute.localizable) {
                    info += i18n.getFlag(key.shift());
                }
                if (attribute.scopable) {
                    info = '<span>' + key.shift() + '</span>' + info;
                }
                if (0 < key.length) {
                    info = key.join(' ') + info;
                }
                if (info) {
                    info = '<span class="attribute-info">' + info + '</span>';
                }

                return label + info;
            },
            addAction: function (code, element) {
                this.actions[code] = element;
            },
            expandHistory: function () {
                this.getParent().openFullPanel();
                this.render();
            },
            collapseHistory: function () {
                this.getParent().closeFullPanel();
                this.render();
            },
            toggleVersion: function (event) {
                var $row = $(event.currentTarget);
                var $body = $row.parent();
                $body.find('tr.changeset').addClass('hide');
                $body.find('i.icon-chevron-down').toggleClass('icon-chevron-right icon-chevron-down');

                if (!$row.hasClass('expanded')) {
                    $row.next('tr.changeset').removeClass('hide');
                    $row.find('i').toggleClass('icon-chevron-right icon-chevron-down');
                }
                $row.siblings().removeClass('expanded');
                $row.toggleClass('expanded');
            }
        });
    }
);
