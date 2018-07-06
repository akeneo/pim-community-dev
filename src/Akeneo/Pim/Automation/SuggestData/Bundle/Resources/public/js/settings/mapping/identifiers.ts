import * as _ from "underscore";
import BaseView = require('pimenrich/js/view/base');

const __ = require('oro/translator');
const template = require('pimee/template/settings/mapping/identifiers');

interface EditIdentifiersMappingConfig {
}

/**
 * Maps pim.ai identifiers with akeneo attributes.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditIdentifiersMappingView extends BaseView {
    readonly template = _.template(template);

    readonly identifiers = [
        {'label':__('akeneo_suggest_data.settings.index.tab.identifiers.headers.brand_label')},
        {'label':'MPN'},
        {'label':'UPC'},
        {'label':'ASIN'},
    ];

    readonly headers = {
        'identifiersLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.identifiers_label'),
        'attributeGroupLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.attribute_group_label'),
        'attributeLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.attribute_label'),
        'suggestDataLabel': __('akeneo_suggest_data.settings.index.tab.identifiers.headers.suggest_data_label'),
    };

    readonly config: EditIdentifiersMappingConfig = {
    };

    /**
     * {@inheritdoc}
     */
    constructor(options: { config: EditIdentifiersMappingConfig }) {
        super(options);

        this.config = {...this.config, ...options.config};
    };

    /**
     * {@inheritdoc}
     */
    public render(): BaseView {
        this.$el.html(this.template({
            headers: this.headers,
            identifiers: this.identifiers,
        }));

        return this;
    }
}

export = EditIdentifiersMappingView;
