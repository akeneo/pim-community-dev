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
        'oro/mediator',
        'backbone',
        'pim/form',
        'routing',
        'text!pim/template/product/meta/groups',
        'text!pim/template/product/meta/group-modal',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/group-manager',
        'oro/navigation',
        'pim/i18n',
        'backbone/bootstrap-modal'
    ],
    function (
        $,
        _,
        mediator,
        Backbone,
        BaseForm,
        Routing,
        formTemplate,
        modalTemplate,
        UserContext,
        FetcherRegistry,
        GroupManager,
        Navigation,
        i18n
    ) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'product-groups',
            template: _.template(formTemplate),
            modalTemplate: _.template(modalTemplate),
            events: {
                'click a[data-group]': 'displayModal'
            },
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                GroupManager.getProductGroups(this.getFormData()).done(function (groups) {
                    this.$el.html(
                        this.template({
                            groups: groups,
                            locale: UserContext.get('catalogLocale')
                        })
                    );
                }.bind(this));

                return this;
            },
            getProductList: function (groupCode) {
                return $.getJSON(
                    Routing.generate('pim_enrich_group_rest_list_products', { identifier: groupCode })
                ).then(_.identity);
            },
            displayModal: function (event) {
                GroupManager.getProductGroups(this.getFormData()).done(function (groups) {
                    var group = _.findWhere(groups, { code: event.currentTarget.dataset.group });

                    $.when(
                        this.getProductList(group.code),
                        FetcherRegistry.getFetcher('attribute').getIdentifierField()
                    ).done(function (productList, identifier) {
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
                    }.bind(this));
                }.bind(this));
            }
        });

        return FormView;
    }
);
