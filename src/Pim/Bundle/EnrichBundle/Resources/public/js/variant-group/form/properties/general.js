'use strict';

/**
 * Module used to display the generals properties of a variant group
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/template/variant-group/tab/properties/general',
        'jquery.select2'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    model: this.getFormData(),
                    sectionTitle: __('pim_enrich.form.variant_group.tab.properties.general'),
                    codeLabel: __('pim_enrich.form.variant_group.tab.properties.code'),
                    typeLabel: __('pim_enrich.form.variant_group.tab.properties.type'),
                    axisLabel: __('pim_enrich.form.variant_group.tab.properties.axis'),
                    __: __
                }));

                this.$el.find('select.select2').select2({});

                this.renderExtensions();
            }
        });
    }
);
