/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import {getConfiguration, getConnectionStatus} from '../../fetcher/franklin-connection';
import ConnectionStatus from '../../model/connection-status';
const __ = require('oro/translator');
const ConnectionSaver = require('akeneo/suggest-data/saver/franklin-connection');
const Messenger = require('oro/messenger');
const template = require('pimee/template/franklin-connection/edit');

interface Config {
  tokenLabelContent: string;
  tokenFieldTitle: string;
  tokenFieldPlaceholder: string;
  tokenSavePreActivationTitle: string;
  tokenSavePostActivationTitle: string;
}

/**
 * Setups the connection to Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class EditView extends BaseView {
  private readonly template: any = _.template(template);

  private readonly config: Config = {
    tokenLabelContent: '',
    tokenFieldTitle: '',
    tokenFieldPlaceholder: '',
    tokenSavePreActivationTitle: '',
    tokenSavePostActivationTitle: '',
  };

  private storedToken: string = '';
  private isConnectionActivated: boolean = false;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

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
    return $.when(
      getConfiguration().then((configuration: any) => {
        const data = {token: ''};
        if (configuration.hasOwnProperty('values')) {
          data.token = configuration.values.token;
        }

        this.setData(data);
        this.storedToken = data.token;
      }),
      BaseView.prototype.configure.apply(this, arguments),
    );
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    getConnectionStatus().then((connectionStatus: ConnectionStatus) => {
      const formData = this.getFormData();

      this.isConnectionActivated = connectionStatus.isActive;
      true === connectionStatus.isActive
        ? this.renderActivatedConnection(formData.token)
        : this.renderUnactivatedConnection(formData.token);
    });

    return this;
  }

  /**
   * Activates the connection to Franklin
   */
  public activate(): void {
    const data = this.getFormData();

    ConnectionSaver
      .save(null, data)
      .fail((xhr: any) => {
        Messenger.notify('error', __(xhr.responseJSON.message));
        this.renderUnactivatedConnection(data.token);
      })
      .done((response: any) => {
        Messenger.notify('success', __(response.message));

        this.storedToken = data.token;
        this.isConnectionActivated = true;
        this.renderActivatedConnection(data.token);
      });
  }

  /**
   * Updates the model.
   * The state of the activation button will be changed too.
   */
  public updateModel(): void {
    const fieldValue = $('.token-field').val();

    const token: string = undefined === fieldValue ? '' : fieldValue.toString();

    this.setData({token});

    if (true === this.isConnectionActivated) {
      this.storedToken !== token
        ? this.buttonAllowedToActivateConnection()
        : this.buttonDisallowedToActivateConnection();
    }
  }

  /**
   * Renders the view for an invalid or empty token.
   *
   * @param {string} token
   */
  private renderUnactivatedConnection(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelContent: __(this.config.tokenLabelContent),
        tokenFieldTitle: __(this.config.tokenFieldTitle),
        tokenFieldPlaceholder: __(this.config.tokenFieldPlaceholder),
        token,
        activationLabel: __(this.config.tokenSavePreActivationTitle),
        buttonStyle: 'AknButton--slateGrey',
        connectionStatus: 'activate-connection',
      }),
    );
  }

  /**
   * Renders the view for a filled and valid token.
   *
   * @param {string} token
   */
  private renderActivatedConnection(token: string): void {
    this.$el.html(
      this.template({
        tokenLabelContent: __(this.config.tokenLabelContent),
        tokenFieldTitle: __(this.config.tokenFieldTitle),
        tokenFieldPlaceholder: __(this.config.tokenFieldPlaceholder),
        token,
        activationLabel: __(this.config.tokenSavePostActivationTitle),
        buttonStyle: 'AknButton--apply AknButton--disabled',
        connectionStatus: 'connection-activated',
      }),
    );
  }

  /**
   * Makes the button grey with text "Activate" so the user knows that connection
   * to Franklin is not active and new token can be submitted.
   */
  private buttonAllowedToActivateConnection() {
    $('.suggest-data-connection')
      .removeClass('AknButton--apply ')
      .removeClass('AknButton--disabled')
      .removeClass('connection-activated')
      .addClass('AknButton--slateGrey')
      .addClass('activate-connection')
      .html(__(this.config.tokenSavePreActivationTitle));
  }

  /**
   * Makes the button green with text "Activated" so the user knows that
   * connection to Franklin is already active.
   */
  private buttonDisallowedToActivateConnection() {
    $('.suggest-data-connection')
      .removeClass('AknButton--slateGrey')
      .removeClass('activate-connection')
      .addClass('AknButton--apply ')
      .addClass('AknButton--disabled')
      .addClass('connection-activated')
      .html(__(this.config.tokenSavePostActivationTitle));
  }
}

export = EditView;
