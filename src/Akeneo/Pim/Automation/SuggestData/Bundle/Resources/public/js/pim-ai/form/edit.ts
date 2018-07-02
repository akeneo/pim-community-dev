import * as _ from 'underscore';
import BaseView = require('pimenrich/js/view/base');
import {EventsHash} from 'backbone';

const __ = require('oro/translator');
const Messenger = require('oro/messenger');
const Routing = require('routing');
const template = require('pimee/template/pim-ai-connection/edit');

interface EditConfig {
  token_label_title: string;
  token_label_content: string;
  token_field_title: string;
  token_field_placeholder: string;
  token_save_pre_activation_title: string;
  token_save_post_activation_title: string;
  get_configuration_url: string;
  post_configuration_url: string;
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

  readonly config: EditConfig = {
    token_label_title: '',
    token_label_content: '',
    token_field_title: '',
    token_field_placeholder: '',
    token_save_pre_activation_title: '',
    token_save_post_activation_title: '',
    get_configuration_url: '',
    post_configuration_url: '',
    code: '',
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: EditConfig }) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .activate-connection': 'activate',
      'keyup input': 'updateModel',
    };
  }

  /**
   * {@inheritdoc}
   */
  public configure(): JQueryPromise<any> {
    const url = Routing.generate(
      this.config.get_configuration_url, {code: this.config.code}
    );

    return $.when(
      $.get(url).then((configuration) => {
        const data = {token: ''};
        if (configuration.hasOwnProperty('values')) {
          data.token = configuration.values.token;
        }

        this.setData(data);
      }),
      BaseView.prototype.configure.apply(this, arguments)
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const formData = this.getFormData();

    0 === Object.keys(formData).length
      ? this.renderUnactivated(formData.token)
      : this.renderActivated(formData.token);

    return this;
  };

  /**
   * Activates the connection to PIM.ai
   */
  public activate(): void {
    const url = Routing.generate(
      this.config.post_configuration_url, {code: this.config.code}
    );

    $.post(
      url,
      JSON.stringify(this.getFormData())
    ).fail((xhr) => {
      Messenger.notify('error', xhr.responseJSON.message);
    }).done((response) => {
      Messenger.notify('success', response.message);
    });
  }

  /**
   * Updates the model.
   */
  public updateModel(): void {
    const fieldValue = $('.token-field').val();

    this.setData({token: fieldValue});
  }

  /**
   * Renders the view for an invalid or empty token.
   *
   * @param {string} token
   */
  private renderUnactivated(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelTitle: __(this.config.token_label_title),
        tokenLabelContent: __(this.config.token_label_content),
        tokenFieldTitle: __(this.config.token_field_title),
        tokenFieldPlaceholder: __(this.config.token_field_placeholder),
        token: token,
        activationLabel: __(this.config.token_save_pre_activation_title),
        buttonStyle: 'AknButton--slateGrey',
      })
    );
  }

  /**
   * Renders the view for a filled and valid token.
   *
   * @param {string} token
   */
  private renderActivated(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelTitle: __(this.config.token_label_title),
        tokenLabelContent: __(this.config.token_label_content),
        tokenFieldTitle: __(this.config.token_field_title),
        tokenFieldPlaceholder: __(this.config.token_field_placeholder),
        token: token,
        activationLabel: __(this.config.token_save_post_activation_title),
        buttonStyle: 'AknButton--apply',
      })
    );
  }
}

export = EditView;
