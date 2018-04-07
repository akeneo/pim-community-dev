const BaseForm = require('pim/form');
const _ = require('underscore');
const __ = require('oro/translator');
const template = require('pim/template/catalog-volume/header');

interface HeaderConfig {
    config: {
        title: string
        description: string
    }
}

class HeaderView extends BaseForm {
    readonly headerTemplate: string = _.template(template);

    initialize(options: HeaderConfig): any {
        this.config = Object.assign({}, options.config);

        return BaseForm.prototype.initialize.apply(this, arguments);
    }

    render(): any {
        const data = this.getRoot().getFormData();
        const product_values: { value: number } = data.product_values;
        const product_values_average: { value: number } = data.product_values_average;

        if (undefined !== product_values && product_values.value > 0) {
            const headerContents: string = this.template({
                title: __(this.config.title)
                    .replace('{{values}}', product_values.value.toLocaleString('en', { useGrouping: true }))
                    .replace('{{average}}', product_values_average.value),
                description: __(this.config.description)
            })

            this.$el.html(headerContents);
        }
    }
}

module.exports = HeaderView;
