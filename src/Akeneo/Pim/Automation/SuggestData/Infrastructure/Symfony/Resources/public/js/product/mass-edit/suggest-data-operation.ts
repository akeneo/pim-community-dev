import * as $ from 'jquery';
import * as _ from 'underscore';

const __ = require('oro/translator');
const Operation = require('pim/mass-edit-form/product/operation');
const template = require('pimee/template/product/mass-edit/suggest-data');

interface SuggestDataOperationConfig {
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
  public readonly template: any = _.template(template);
  public readonly config: SuggestDataOperationConfig = {
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
  constructor(options: { config: SuggestDataOperationConfig }) {
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
  public render() {
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
