/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import * as _ from 'underscore';
import {getConnectionStatus} from '../../fetcher/franklin-connection';
import ConnectionStatus from '../../model/connection-status';
const __ = require('oro/translator');
const Operation = require('pim/mass-edit-form/product/operation');
const template = require('akeneo/suggest-data/template/mass-edit/suggest-data-operation');
const Router = require('pim/router');

interface Config {
  title: string;
  label: string;
  subLabel: string;
  description: string;
  code: string;
  jobInstanceCode: string;
  icon: string;
  illustration: string;
  subscribeLabel: string;
  unsubscribeLabel: string;
  franklinActivationConstraint: string;
  invalidMappingConstraint: string;
}

/**
 * Mass operation to subscribe/unsubscribe products to Franklin.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestDataOperation extends Operation {
  /**
   * @returns {boolean}
   */
  private static redirectToFranklinConnection(): boolean {
    Router.redirectToRoute('akeneo_suggest_data_connection_edit');

    return false;
  }

  private readonly template: any = _.template(template);
  private readonly config: Config = {
    title: '',
    label: '',
    subLabel: '',
    description: '',
    code: '',
    jobInstanceCode: '',
    icon: '',
    illustration: '',
    subscribeLabel: '',
    unsubscribeLabel: '',
    franklinActivationConstraint: '',
    invalidMappingConstraint: '',
  };

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
  public events(): Backbone.EventsHash {
    return {
      'click .operation-type': this.switchAction,
      'click [data-action]': SuggestDataOperation.redirectToFranklinConnection,
    };
  }

  /**
   * {@inheritdoc}
   */
  public render() {
    getConnectionStatus().then((connectionStatus: ConnectionStatus) => {
      if (undefined === this.getFormData().action) {
        this.setAction('subscribe');
      }

      let errorMessage = '';
      if (!connectionStatus.isIdentifiersMappingValid) {
        errorMessage = __(this.config.invalidMappingConstraint);
        this.getParent().disableNextButton();
      }
      if (!connectionStatus.isActive) {
        errorMessage = __(this.config.franklinActivationConstraint);
        this.getParent().disableNextButton();
      }

      this.$el.html(this.template({
        subscribeLabel: __(this.config.subscribeLabel),
        unsubscribeLabel: __(this.config.unsubscribeLabel),
        errorMessage,
        currentAction: this.getFormData().action,
        isActive: connectionStatus.isActive && connectionStatus.isIdentifiersMappingValid,
      }));

      this.delegateEvents();
    });
  }

  /**
   * @param event
   */
  private switchAction(event: { target: any }): void {
    const action: string = $(event.target).attr('data-value') as string;
    const $button = $(event.target).parent().find('.AknButton--apply');

    this.setAction(action);
    $button.removeClass('AknButton--apply');
    $(event.target).addClass('AknButton--apply');
  }

  /**
   * @param {string} action
   */
  private setAction(action: string): void {
    const data = this.getFormData();

    data.jobInstanceCode = this.config.jobInstanceCode.replace('%s', action);
    data.action = action;

    this.setData(data);
  }
}

export = SuggestDataOperation;
