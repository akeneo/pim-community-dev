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
import {getConnectionStatus, ConnectionStatus} from '../../fetcher/franklin-connection';

const __ = require('oro/translator');
const Operation = require('pim/mass-edit-form/product/operation');
const template = require('pimee/template/mass-edit/suggest-data-operation');
const readOnlyTemplate = require('pimee/template/mass-edit/suggest-data-operation-read-only');
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
}

/**
 * Mass operation to subscribe/unsubscribe products to Franklin.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestDataOperation extends Operation {
  private readonly template: any = _.template(template);
  private readonly readOnlyTemplate: any = _.template(readOnlyTemplate);
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
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super({
      ...options, ...{
        className: 'AknButtonList AknButtonList--single',
      },
    });

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): Backbone.EventsHash {
    return {
      'click .AknButton': 'switchAction',
      'click [data-action]': 'redirectToFranklinConnection',
    };
  }

  /**
   * {@inheritdoc}
   */
  public configure() {
    // getConnectionStatus().then((connectionStatus: ConnectionStatus) => {
    //   if (!connectionStatus.isActive) {
    //     this.setReadOnly(true);
    //   }
    // });

    return Operation.prototype.configure.apply(this, arguments);
  }
  /**
   * {@inheritdoc}
   */
  public render(): object {
    return getConnectionStatus().then((connectionStatus: ConnectionStatus) => {
      if (!connectionStatus.isActive) {
        this.$el.html(this.readOnlyTemplate({
          subscribeLabel: __(this.config.subscribeLabel),
          unsubscribeLabel: __(this.config.unsubscribeLabel),
          errorMessage: __(this.config.franklinActivationConstraint),
        }));

        return this;
      }

      if (undefined === this.getFormData().action) {
        this.setAction('subscribe');
      }

      this.$el.html(this.template({
        subscribeLabel: __(this.config.subscribeLabel),
        unsubscribeLabel: __(this.config.unsubscribeLabel),
        currentAction: this.getFormData().action,
      }));

      return this;
    });
  }

  /**
   * @param event
   */
  protected switchAction(event: any): void {
    const action: string = $(event.target).attr('data-value') as string;
    const $button = $(event.target).parent().find('.AknButton--apply');

    this.setAction(action);
    $button.removeClass('AknButton--apply');
    $(event.target).addClass('AknButton--apply');
  }

  /**
   * @param {string} action
   */
  protected setAction(action: string): void {
    const data = this.getFormData();

    data.jobInstanceCode = this.config.jobInstanceCode.replace('%s', action);
    data.action = action;

    this.setData(data);
  }

  /**
   * @param event
   */
  protected redirectToFranklinConnection(): any {
    // const toto = Router.redirect('akeneo_suggest_data_connection_edit');
    Router.redirect('akeneo_suggest_data_connection_edit');

    return;
  }
}

export = SuggestDataOperation;
