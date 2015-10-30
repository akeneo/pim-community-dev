'use strict';
/**
 * Add attribute extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'backbone',
        'underscore',
        'pim/form',
        'pim/attribute-manager',
        'text!pim/template/product/tab/attribute/add-attribute',
        'pim/user-context',
        'pim/fetcher-registry',
        'oro/loading-mask',
        'jquery.multiselect',
        'jquery.multiselect.filter'
    ],
    function ($, Backbone, _, BaseForm, AttributeManager, template, UserContext, FetcherRegistry, LoadingMask) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'add-attribute',
            template: _.template(template),

            /**
             * Render this extension
             *
             * @return {Object}
             */
            render: function () {
                this.$el.html($('<select>').attr('multiple', true));

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

            /**
             * Initialize jQuery multiselect and its filter plugin
             */
            initializeSelectWidget: function () {
                var $select = this.$('select');

                var opts = {
                    title: _.__('pim_enrich.form.product.tab.attributes.btn.add_attributes'),
                    placeholder: _.__('pim_enrich.form.product.tab.attributes.info.search_attributes'),
                    emptyText: _.__('pim_enrich.form.product.tab.attributes.info.no_available_attributes'),
                    header: '',
                    height: 175,
                    minWidth: 225,
                    classes: 'pimmultiselect pim-add-attributes-multiselect',
                    position: {
                        my: 'right top',
                        at: 'right bottom',
                        collision: 'none'
                    },
                    open: function () {
                        var loadingMask = this.showLoadingMask();
                        this.loadAttributesChoices()
                            .always(function () {
                                loadingMask.hide().$el.remove();
                            });
                    }.bind(this)
                };
                opts.selectedText     = opts.title;
                opts.noneSelectedText = opts.title;

                $select
                    .multiselect(opts)
                    .multiselectfilter({
                        label: false,
                        placeholder: opts.placeholder
                    });
                var $menu = $('.ui-multiselect-menu.pimmultiselect');

                var $footerContainer = $('<div>', { 'class': 'ui-multiselect-footer' });
                var $saveButton = $('<a>', {
                    'class': 'btn btn-small',
                    text: _.__('pim_enrich.form.product.tab.attributes.btn.add')
                }).on('click', function () {
                    $select.multiselect('close');
                    var values = $select.val();
                    if (null !== values) {
                        this.addAttributes(values);
                    }
                }.bind(this));

                $footerContainer.append($saveButton);
                $menu.append($footerContainer);

                $select.next()
                    .addClass('btn btn-group')
                    .append($('<span>', { 'class': 'caret' }))
                    .removeAttr('style');

                $menu.find('input[type="search"]').width(200);

                var $content = $menu.find('.ui-multiselect-checkboxes');
                if (!$content.html()) {
                    $content.html(
                        $('<span>', {
                            text: opts.emptyText,
                            css: {
                                'position': 'absolute',
                                'color': '#999',
                                'padding': '15px',
                                'font-size': '13px'
                            }
                        })
                    );
                }
            },

            /**
             * Add the specified attributes to the product
             *
             * @param {Array} attributeCodes
             */
            addAttributes: function (attributeCodes) {
                this.trigger('add-attribute:add', { codes: attributeCodes });
            },

            /**
             * Fetch attributes and refresh the multiselect with the new choices
             *
             * @return {Promise}
             */
            loadAttributesChoices: function () {
                return $.when(
                    AttributeManager.getAvailableOptionalAttributes(this.getFormData()),
                    FetcherRegistry.getFetcher('attribute-group').fetchAll()
                ).then(
                    function (attributes, attributeGroups) {
                        this.$('select')
                            .html(this.template({
                                groupedAttributes: this.buildGroupedAttributes(attributes, attributeGroups),
                                locale: UserContext.get('catalogLocale')
                            }))
                            .multiselect('refresh')
                            .next('button').removeAttr('style');
                    }.bind(this)
                );
            },

            /**
             * Organize attributes by attribute groups
             *
             * @param {Array} attributes
             * @param {Object} attributeGroups
             *
             * @return {Object}
             */
            buildGroupedAttributes: function (attributes, attributeGroups) {
                var attributeCodes = _.pluck(attributes, 'code');
                var groups         = _.sortBy($.extend(true, {}, attributeGroups), 'sortOrder');

                _.each(groups, function (group) {
                    group.attributes = _.intersection(group.attributes, attributeCodes);
                    group.attributes = _.map(group.attributes, function (attributeCode) {
                        return _.findWhere(attributes, { code: attributeCode });
                    });
                });

                return groups;
            },

            /**
             * Create, insert, show and return the loading mask for multiselect choices list
             *
             * @return {Object}
             */
            showLoadingMask: function () {
                var loadingMask = new LoadingMask();
                $('.ui-widget-content.pim-add-attributes-multiselect .ui-multiselect-checkboxes')
                    .empty()
                    .append(loadingMask.render().$el);
                loadingMask.show();

                return loadingMask;
            }
        });
    }
);
