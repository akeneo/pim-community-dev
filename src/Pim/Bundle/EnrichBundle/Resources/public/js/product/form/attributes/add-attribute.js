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
        'pim/formatter/choices/base',
        'jquery.multiselect',
        'jquery.multiselect.filter'
    ],
    function ($, Backbone, _, BaseForm, AttributeManager, template, UserContext, FetcherRegistry, LoadingMask, ChoicesFormatter) {

        return BaseForm.extend({
            tagName: 'div',
            className: 'add-attribute',
            template: _.template(template),
            defaultOptions: {
                title: _.__('pim_enrich.form.product.tab.attributes.btn.add_attributes'),
                placeholder: _.__('pim_enrich.form.product.tab.attributes.info.search_attributes'),
                buttonTitle: _.__('pim_enrich.form.product.tab.attributes.btn.add'),
                emptyText: _.__('pim_enrich.form.product.tab.attributes.info.no_available_attributes'),
                header: '',
                height: 175,
                minWidth: 225,
                classes: 'pim-add-attributes-multiselect',
                position: {
                    my: 'right top',
                    at: 'right bottom',
                    collision: 'none'
                }
            },
            resultsPerPage: 20,

            /**
             * Render this extension
             *
             * @return {Object}
             */
            render: function () {
                this.$el.html(this.template());

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

            /**
             * Initialize jQuery multiselect and its filter plugin
             */
            initializeSelectWidget: function () {
                var $select = this.$('input[type="hidden"]');
                var opts = this.defaultOptions;
                var queryTimer;

                $select.select2({
                    tags: false,
                    multiple: true,
                    closeOnSelect: false,
                    allowClear: true,
                    minimumInputLength: 2,
                    query: function (options) {
                        window.clearTimeout(queryTimer);
                        queryTimer = window.setTimeout(function () {
                            var page = 1;
                            if (options.context && options.context.page) {
                                page = options.context.page;
                            }
                            var searchOptions = {
                                search: options.term,
                                options: {
                                    limit: this.resultsPerPage,
                                    page: page
                                }
                            };

                            $.when(
                                FetcherRegistry.getFetcher('attribute').search(searchOptions)
                            ).then(function(attributes) {
                                var choices = ChoicesFormatter.format(attributes);
                                options.callback({
                                    results: choices,
                                    more: choices.length === this.resultsPerPage,
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }.bind(this));
                        }.bind(this), 400)
                    }.bind(this)
                });

                var $menu = this.$('.select2-drop');

                var $footerContainer = $('<div>', {'class': 'ui-multiselect-footer'});
                var $saveButton = $('<a>', {
                    'class': 'btn btn-small',
                    text: this.defaultOptions.buttonTitle
                }).on('click', function () {
                    $select.select2('close');
                    var values = $select.select2('val');
                    if (null !== values) {
                        this.addAttributes(values);
                    }
                }.bind(this));

                $footerContainer.append($saveButton);
                $menu.append($footerContainer);

                $select.next()
                    .addClass('btn btn-group')
                    .append($('<span>', {'class': 'caret'}))
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
            }
        });
    }
);
