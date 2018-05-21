import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';

const __ = require('oro/translator');
const template = require('pim/template/catalog-volume/header');

interface HeaderConfig {
  title: string;
  description: string;
}

/**
 * Header view for catalog volume screen
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HeaderView extends BaseView {
  readonly headerTemplate = _.template(template);
  public config: HeaderConfig;

  /**
   * Initialize
   *
   * @param options
   */
  constructor(options: {config: HeaderConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const data = this.getRoot().getFormData();
    const productValues: {value: number} = data.count_product_and_product_model_values;
    const productValuesAverage: {value: {max: number, average: number}} = data.average_max_product_and_product_model_values;

    if (undefined !== productValues && productValues.value > 0) {
      const headerContents: string = this.headerTemplate({
        title: __(this.config.title)
          .replace('{{values}}', productValues.value.toLocaleString('en', {useGrouping: true}))
          .replace('{{average}}', productValuesAverage.value.average),
        description: __(this.config.description),
      });

      this.$el.html(headerContents);
    }

    return this;
  }
}

export = HeaderView;
