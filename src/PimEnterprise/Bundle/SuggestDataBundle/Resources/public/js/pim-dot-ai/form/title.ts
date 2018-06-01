import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
// import Routing = require('routing');

const __ = require('oro/translator');
const template = require('pimee/template/pim-dot-ai-connection/title');

interface SectionConfig {
  main_title: string;
  sub_title: string;
  illustration: string;
  description:  Array<string>;
  identifiers_mapping_url: string;
}

/**
 * Setups the connection to PIM.AI.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditView extends BaseView {
  readonly template = _.template(template);
  readonly config: SectionConfig = {
    main_title: '',
    sub_title: '',
    illustration: '',
    description: [],
    identifiers_mapping_url: '',
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: SectionConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const translatedParagraph: Array<string> = this.config.description.map(translationKey => __(translationKey));
    const description: string = '<p>' + translatedParagraph.join('</p><p>') + '</p>';
    const subTitle: string = '<p>' + __(this.config.sub_title) + '</p>';

    this.$el.empty().html(
        this.template({
          mainTitle: __(this.config.main_title),
          subTitle: subTitle,
          illustration: this.config.illustration,
          description: description,
        })
    );

    return this;
  };
}

export = EditView;
