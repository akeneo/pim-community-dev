

import _ from 'underscore';
import MultiselectField from 'pim/multi-select-field';
import Routing from 'routing';
import FetcherRegistry from 'pim/fetcher-registry';
export default MultiselectField.extend({
    fieldType: 'reference-multi-select',
    getTemplateContext: function () {
        return MultiselectField.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.userCanAddOption = false;

                        return templateContext;
                    });
    },
    getChoiceUrl: function () {
        return FetcherRegistry.getFetcher('reference-data-configuration').fetchAll()
                    .then(_.bind(function (config) {
                        return Routing.generate(
                            'pim_ui_ajaxentity_list',
                            {
                                'class': config[this.attribute.reference_data_name].class,
                                'dataLocale': this.context.locale,
                                'collectionId': this.attribute.id,
                                'options': {'type': 'code'}
                            }
                        );
                    }, this));
    }
});

