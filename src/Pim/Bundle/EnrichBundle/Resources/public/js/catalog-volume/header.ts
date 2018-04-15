import BaseView = require('pimenrich/js/view/base')

const _ = require('underscore');
const __ = require('oro/translator');
const template = require('pim/template/catalog-volume/header');

interface HeaderConfig {
    title: string
    description: string
}

/**
 * Header view for catalog volume screen
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HeaderView extends BaseView {
    readonly headerTemplate =  _.template(template);
    public config: HeaderConfig;

    /**
     * Initialize
     *
     * @param options
     */
    constructor(options: { config: HeaderConfig }) {
        super(options);

        this.config = Object.assign({}, options.config);
    }

    /**
     * {@inheritdoc}
     */
    render(): any {
        const data = this.getRoot().getFormData();
        const product_values: { value: number } = data.product_values;
        const product_values_average: { value: number } = data.product_values_average;

        if (undefined !== product_values && product_values.value > 0) {
            const headerContents: string = this.headerTemplate({
                title: __(this.config.title)
                    .replace('{{values}}', product_values.value.toLocaleString('en', { useGrouping: true }))
                    .replace('{{average}}', product_values_average.value),
                description: __(this.config.description)
            })

            this.$el.html(headerContents);
        }
    }
}

export = HeaderView;
