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

const __ = require('oro/translator');
const Operation = require('pim/mass-edit-form/product/operation');
const template = require('pimee/template/mass-edit/suggest-data-operation');

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
}

/**
 * Mass operation to subscribe/unsubscribe products to Franklin.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestDataOperation extends Operation {
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
    };
  }

  /**
   * {@inheritdoc}
   */
  public render(): object {
    if (undefined === this.getFormData().action) {
      this.setAction('subscribe');
    }

    this.$el.html(this.template({
      subscribeLabel: __(this.config.subscribeLabel),
      unsubscribeLabel: __(this.config.unsubscribeLabel),
      currentAction: this.getFormData().action,
    }));

    return this;
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
}

export = SuggestDataOperation;
