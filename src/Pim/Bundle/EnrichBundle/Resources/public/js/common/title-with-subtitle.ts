import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
// import Routing = require('routing');

const __ = require('oro/translator');
const template = require('pim/template/common/title-with-subtitle');

interface TitleWithSubTitleConfig {
  main_title: string;
  sub_title: string;
  illustration: string;
  description:  Array<string>;
}

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TitleWithSubtitleView extends BaseView {
  readonly template = _.template(template);
  readonly config: TitleWithSubTitleConfig = {
    main_title: '',
    sub_title: '',
    illustration: '',
    description: [],
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: TitleWithSubTitleConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const translatedDescriptionLines: Array<string> = this.config.description.map(translationKey => __(translationKey));

    this.$el.empty().html(
        this.template({
          mainTitle: __(this.config.main_title),
          subTitle: __(this.config.sub_title),
          illustration: this.config.illustration,
          descriptionLines: translatedDescriptionLines,
        })
    );

    return this;
  };
}

export = TitleWithSubtitleView;
