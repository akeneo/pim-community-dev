

/**
 * Attributes used as label field view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import FetcherRegistry from 'pim/fetcher-registry';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import SecurityContext from 'pim/security-context';
import template from 'pim/template/family/tab/general/attribute-as-label';
import 'jquery.select2';
export default BaseForm.extend({
    className: 'AknFieldContainer',
    template: _.template(template),
    errors: [],
    catalogLocale: UserContext.get('catalogLocale'),

            /**
             * {@inheritdoc}
             */
    initialize: function (config) {
        this.config = config.config;
    },

            /**
             * {@inheritdoc}
             */
    configure: function () {
        return BaseForm.prototype.configure.apply(this, arguments);
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        if (!this.configured) {
            return this;
        }

        this.$el.html(this.template({
            i18n: i18n,
            catalogLocale: this.catalogLocale,
            attributes: _.filter(
                        this.getFormData().attributes,
                        function (attribute) {
                            return attribute.type === 'pim_catalog_text' ||
                            attribute.type === 'pim_catalog_identifier';
                        }
                    ),
            currentAttribute: this.getFormData().attribute_as_label,
            fieldBaseId: this.config.fieldBaseId,
            errors: this.errors,
            label: __(this.config.label),
            requiredLabel: __('pim_enrich.form.required'),
            isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties')
        }));

        this.$('.select2').select2().on('change', this.updateState.bind(this));

        this.renderExtensions();
    },

            /**
             * Update object state on property change
             *
             * @param event
             */
    updateState: function (event) {
        var data = this.getFormData();
        data.attribute_as_label = event.currentTarget.value;
        this.setData(data);
    }
});

