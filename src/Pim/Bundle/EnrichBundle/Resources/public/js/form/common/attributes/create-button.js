'use strict';

/**
 * Create attribute button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/form/tab/attribute/create-button',
        'text!pim/template/form/tab/attribute/create-modal-content',
        'routing',
        'pim/fetcher-registry',
        'oro/navigation',
        'module',
        'backbone/bootstrap-modal'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        templateModal,
        Routing,
        FetcherRegistry,
        navigation,
        module
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateModal: _.template(templateModal),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Create the dialog modal and bind clicks
             */
            createModal: function (attributeTypesMap) {

                var attributeTypes = this.formatAndSortAttributeTypesByLabel(attributeTypesMap);

                var modal = null;
                var modalContent = this.templateModal({
                    attributeTypes: attributeTypes,
                    iconsMap: module.config().attribute_icons,
                    generateRoute: function (route, params) {
                        return Routing.generate(route, params);
                    }
                });
                var modalTitle = __(this.config.modalTitle);

                $('#attribute-create-button').on('click', function () {
                    if (modal) {
                        modal.open();
                    } else {
                        modal = new Backbone.BootstrapModal({
                            title: modalTitle,
                            content: modalContent
                        });

                        modal.open();
                        modal.$el.find('.modal-footer').remove();

                        modal.$el.on('click', 'a.attribute-choice', function (e) {
                            e.preventDefault();
                            modal.close();
                            modal.$el.remove();
                            navigation.getInstance().navigate('#url=' + $(this).attr('href'), {trigger: true});
                        });
                    }
                });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('attribute-type')
                    .fetchAll()
                    .then(function (attributeTypes) {

                        this.$el.html(this.template({
                            buttonTitle: __(this.config.buttonTitle)
                        }));

                        this.createModal(attributeTypes);
                    }.bind(this));

                return this;
            },

            /**
             * Format the map to an array and sort attributeTypes by label
             * @param attributeTypesMap => Map of attributeTypes
             * @returns {Array}
             */
            formatAndSortAttributeTypesByLabel: function (attributeTypesMap) {

                var sortedAttributeTypesByLabel = [];
                for (var key in attributeTypesMap) {
                    sortedAttributeTypesByLabel.push({
                        code: key,
                        label: __('pim_enrich.entity.attribute_label.' + attributeTypesMap[key])
                    });
                }

                sortedAttributeTypesByLabel.sort(function (a, b) {
                    return a.label > b.label ? 1 : -1;
                });

                return sortedAttributeTypesByLabel;
            }
        });
    });
