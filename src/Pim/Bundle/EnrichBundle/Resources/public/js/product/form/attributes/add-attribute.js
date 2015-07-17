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
        'jquery.multiselect',
        'jquery.multiselect.filter'
    ],
    function ($, Backbone, _, BaseForm, AttributeManager, template, UserContext, FetcherRegistry) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'add-attribute',
            template: _.template(template),
            state: null,
            initialize: function () {
                this.state = new Backbone.Model({});
                this.on('add-attribute:update:available-attributes', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                return $.when(
                    this.loadAttributeGroups(),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                this.getAvailableAttributes().then(_.bind(function (attributes) {
                    this.$el.empty();
                    this.$el.html(this.template({
                        groupedAttributes: this.getGroupedAttributes(attributes),
                        locale: UserContext.get('catalogLocale')
                    }));

                    this.initializeSelectWidget();
                    this.delegateEvents();
                }, this));

                return this;
            },
            initializeSelectWidget: function () {
                var opts = {
                    title: _.__('pim_enrich.form.product.tab.attributes.btn.add_attributes'),
                    placeholder: _.__('pim_enrich.form.product.tab.attributes.info.search_attributes'),
                    emptyText: _.__('pim_enrich.form.product.tab.attributes.info.no_available_attributes'),
                    header: '',
                    height: 175,
                    minWidth: 225,
                    classes: 'pimmultiselect',
                    position: {
                        my: 'right top',
                        at: 'right bottom',
                        collision: 'none'
                    }
                };
                opts.selectedText     = opts.title;
                opts.noneSelectedText = opts.title;

                var $select = this.$('select');

                $select.multiselect(opts).multiselectfilter({
                    label: false,
                    placeholder: opts.placeholder
                });
                var $menu = $('.ui-multiselect-menu.pimmultiselect');

                var $footerContainer = $('<div>', { 'class': 'ui-multiselect-footer' });
                var $saveButton = $('<a>', {
                    'class': 'btn btn-small',
                    text: _.__('pim_enrich.form.product.tab.attributes.btn.add')
                }).on('click', _.bind(function () {
                        $select.multiselect('close');
                        var values = $select.val();
                        if (values !== null) {
                            this.addAttributes(values);
                        }
                    }, this));

                $footerContainer.append($saveButton);
                $menu.append($footerContainer);

                var $openButton = $('button.pimmultiselect').addClass('btn btn-group');
                $openButton.append($('<span>', { 'class': 'caret' })).removeAttr('style');

                $menu.find('input[type="search"]').width(200);

                var $content = $menu.find('.ui-multiselect-checkboxes');
                if (!$content.html()) {
                    $content.html(
                        $('<span>', { text: opts.emptyText, css: {
                            'position': 'absolute',
                            'color': '#999',
                            'padding': '15px',
                            'font-size': '13px'
                        }})
                    );
                    $saveButton.addClass('disabled');
                }
            },
            addAttributes: function (attributeCodes) {
                this.trigger('add-attribute:add', {codes: attributeCodes});
            },
            getAvailableAttributes: function () {
                return AttributeManager.getAvailableOptionalAttributes(this.getFormData());
            },
            loadAttributeGroups: function () {
                return FetcherRegistry.getFetcher('attributeGroup').fetchAll().done(_.bind(function (attributeGroups) {
                    this.attributeGroups = attributeGroups;
                }, this));
            },
            getGroupedAttributes: function (attributes) {
                var attributeCodes = _.pluck(attributes, 'code');
                var groups = _.sortBy($.extend(true, {}, this.attributeGroups), 'sortOrder');

                _.each(groups, function (group) {
                    group.attributes = _.filter(group.attributes, function (attributeCode) {
                        return attributeCodes.indexOf(attributeCode) !== -1;
                    });

                    group.attributes = _.map(group.attributes, function (attributeCode) {
                        return _.findWhere(attributes, { code: attributeCode });
                    });
                });

                return groups;
            }
        });
    }
);
