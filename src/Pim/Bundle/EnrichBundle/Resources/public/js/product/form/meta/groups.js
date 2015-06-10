 'use strict';
/**
 * Product groups extension
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
        'routing',
        'text!pim/template/product/meta/groups',
        'text!pim/template/product/meta/group-modal',
        'pim/user-context',
        'pim/entity-manager',
        'oro/navigation',
        'pim/i18n',
        'backbone/bootstrap-modal'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        Routing,
        formTemplate,
        modalTemplate,
        UserContext,
        EntityManager,
        Navigation,
        i18n
    ) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),
            modalTemplate: _.template(modalTemplate),
            events: {
                'click a[data-group]': 'displayModal'
            },
            configure: function () {
                this.listenTo(this.getRoot().model, 'change:groups', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.getProductGroups(this.getData()).done(_.bind(function (groups) {
                    this.$el.html(
                        this.template({
                            groups: groups,
                            locale: UserContext.get('catalogLocale')
                        })
                    );

                    this.delegateEvents();
                }, this));

                return this;
            },
            getProductGroups: function (product) {
                var deferred = $.Deferred();

                var promises = [];
                var groups = [];

                _.each(product.groups, function (groupCode) {
                    promises.push(
                        EntityManager.getRepository('group').find(groupCode).done(function (group) {
                            groups.push(group);
                        })
                    );
                });

                if (product.variant_group) {
                    promises.push(
                        EntityManager.getRepository('variantGroup').find(product.variant_group).done(function (group) {
                            groups.push(group);
                        })
                    );
                }

                $.when.apply($, promises).done(function () {
                    deferred.resolve(groups);
                });

                return deferred.promise();
            },
            getProductList: function (groupCode) {
                return $.getJSON(
                    Routing.generate('pim_enrich_group_rest_list_products', { identifier: groupCode })
                ).then(_.identity);
            },
            displayModal: function (event) {
                this.getProductGroups(this.getData()).done(_.bind(function (groups) {
                    var group = _.findWhere(groups, { code: event.currentTarget.dataset.group });

                    $.when(
                        this.getProductList(group.code),
                        EntityManager.getRepository('attribute').getIdentifier()
                    ).done(_.bind(function (productList, identifier) {
                        var groupModal = new Backbone.BootstrapModal({
                            allowCancel: true,
                            okText: _.__('pim_enrich.entity.product.meta.groups.modal.view_group'),
                            cancelText: _.__('pim_enrich.entity.product.meta.groups.modal.close'),
                            title: _.__(
                                'pim_enrich.entity.product.meta.groups.modal.title',
                                { group: i18n.getLabel(group.label, UserContext.get('catalogLocale'), group.code) }
                            ),
                            content: this.modalTemplate({
                                products:     productList.products,
                                productCount: productList.productCount,
                                identifier:   identifier,
                                locale:       UserContext.get('catalogLocale')
                            })
                        });

                        groupModal.on('ok', function visitGroup() {
                            groupModal.close();
                            Navigation.getInstance().setLocation(
                                Routing.generate(
                                    'VARIANT' === group.type ?
                                        'pim_enrich_variant_group_edit' :
                                        'pim_enrich_group_edit',
                                    { id: group.meta.id }
                                )
                            );
                        });
                        groupModal.open();

                        groupModal.$el.on('click', 'a[data-product-id]', function visitProduct(event) {
                            groupModal.close();
                            Navigation.getInstance().setLocation(
                                Routing.generate(
                                    'pim_enrich_product_edit',
                                    { id: event.currentTarget.dataset.productId }
                                )
                            );
                        });
                    }, this));
                }, this));
            }
        });

        // TODO same
        return FormView;
    }
);
