'use strict';

/**
 * Display the list of attribute groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry',
    'pim/common/property',
    'routing',
    'pim/router',
    'pim/user-context',
    'pim/i18n',
    'pim/security-context',
    'pim/template/form/attribute-group/list'
],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        propertyAccessor,
        Routing,
        router,
        UserContext,
        i18n,
        securityContext,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            attributeGroups: [],
            events: {
                'click .attribute-group-link': 'redirectToGroup'
            },

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
                const canSortAttributeGroup = securityContext.isGranted('pim_enrich_attributegroup_sort');
                this.$el.html(this.template({
                    attributeGroups: _.sortBy(_.values(this.attributeGroups), function (attributeGroup) {
                        return attributeGroup.sort_order;
                    }),
                    i18n: i18n,
                    uiLocale: UserContext.get('catalogLocale'),
                    canSortAttributeGroup
                }));

                if (canSortAttributeGroup) {
                    this.$('tbody').sortable({
                        handle: '.handle',
                        containment: 'parent',
                        tolerance: 'pointer',
                        update: this.updateAttributeOrders.bind(this),
                        helper: function (e, tr) {
                            var $originals = tr.children();
                            var $helper = tr.clone();
                            $helper.children().each(function (index) {
                                $(this).width($originals.eq(index).width());
                            });

                            return $helper;
                        }
                    });
                }

                this.renderExtensions();
            },

            /**
             * Update the attribute order based on the dom
             */
            updateAttributeOrders: function () {
                var sortOrder = _.reduce(this.$('.attribute-group'), function (previous, current, order) {
                    var next = _.extend({}, previous);
                    next[current.dataset.attributeGroupCode] = order;

                    return next;
                }, {});

                $.ajax({
                    url: Routing.generate('pim_enrich_attributegroup_rest_sort'),
                    type: 'PATCH',
                    data: JSON.stringify(sortOrder)
                }).then(function (attributeGroups) {
                    this.attributeGroups = attributeGroups;

                    FetcherRegistry.getFetcher('attribute-group').clear();

                    this.render();
                }.bind(this));
            },

            /**
             * Redirect to attribute group page
             *
             * @param {event} event
             */
            redirectToGroup: function (event) {
                if (securityContext.isGranted('pim_enrich_attributegroup_edit')) {
                    router.redirectToRoute(
                        'pim_enrich_attributegroup_edit',
                        {identifier: event.target.dataset.attributeGroupCode}
                    )
                }
            }
        });
    }
);
