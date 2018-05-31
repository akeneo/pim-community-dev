import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
// import Routing = require('routing');

const __ = require('oro/translator');
const template = require('pimee/template/pim-dot-ai-connection/edit');

interface SectionConfig {
  token_label_title: string;
  token_label_content: string;
  token_field_title: string;
  token_field_placeholder: string;
  token_save_pre_activation_title: string;
  token_save_post_activation_title: string;
  activation_url: string;
  code: string;
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
    token_label_title: '',
    token_label_content: '',
    token_field_title: '',
    token_field_placeholder: '',
    token_save_pre_activation_title: '',
    token_save_post_activation_title: '',
    activation_url: '',
    code: '',
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
    const token: string = '';
    const activationLabel: string = 0 == token.length
        ? __(this.config.token_save_pre_activation_title)
        : __(this.config.token_save_post_activation_title);
    const buttonStyle: string = 0 == token.length ? 'AknButton--slateGrey' : 'AknButton--apply';

    // Not used yet
    // const activationUrl = Routing.generate(this.config.activation_url, {code: this.config.code});

    this.$el.empty().html(
        this.template({
          tokenLabelTitle: __(this.config.token_label_title),
          tokenLabelContent: __(this.config.token_label_content),
          tokenFieldTitle: __(this.config.token_field_title),
          tokenFieldPlaceholder: __(this.config.token_field_placeholder),
          token: token,
          activationLabel: activationLabel,
          buttonStyle: buttonStyle,
        })
    );

    return this;
  };
}

export = EditView;
