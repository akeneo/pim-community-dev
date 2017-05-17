'use strict';

/**
 * Display the list of attribute groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/common/property',
        'routing',
        'text!pim/template/form/attribute-group/list'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        propertyAccessor,
        Routing,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            attributeGroups: [],

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return $.when(
                    FetcherRegistry.getFetcher('attribute-group').fetchAll(),
                    BaseForm.prototype.configure.apply(this, arguments)
                ).then(function (attributeGroups) {
                    this.attributeGroups = attributeGroups;
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var config = this.options.config;

                this.$el.html(this.template({
                    attributeGroups: this.attributeGroups
                }));

                this.$('tbody').sortable({
                    handle: '.handle',
                    containment: 'parent',
                    tolerance: 'pointer',
                    update: this.updateAttributeOrders.bind(this),
                    helper: function(e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            $(this).width($originals.eq(index).width());
                        });

                        return $helper;
                    }
                });

                this.renderExtensions();

            },

            updateAttributeOrders: function () {
                var sortOrder = _.reduce(this.$('tbody > tr'), function (previous, current, order) {
                    var next = _.extend({}, previous);
                    next[current.dataset.attributeGroupCode] = order;

                    return next;
                }, {});

                $.ajax({
                    url: Routing.generate('pim_enrich_attributegroup_rest_sort'),
                    type: 'PATCH',
                    data: sortOrder
                }).then(function () {

                });

                this.render();
            }
        });
    }
);
