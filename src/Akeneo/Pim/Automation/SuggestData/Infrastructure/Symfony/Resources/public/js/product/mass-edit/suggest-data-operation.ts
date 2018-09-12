import * as _ from 'underscore';
import * as $ from 'jquery';

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
  readonly template: any = _.template(template);
  readonly config: SuggestDataOperationConfig = {
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
        className: 'AknButtonList AknButtonList--single'
      }
    });

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  configure(): void {
    this.setAction('subscribe');

    Operation.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  public events(): Backbone.EventsHash {
    return {
      'click .AknButton': 'switchAction',
    }
  }

  /**
   * {@inheritdoc}
   */
  public render() {
    this.$el.html(this.template({
      subscribeLabel: __(this.config.subscribeLabel),
      unsubscribeLabel: __(this.config.unsubscribeLabel)
    }));

    return this;
  }

  /**
   * @param event
   */
  protected switchAction(event: any): void {
    const action: string = <string> $(event.target).attr('data-value');
    const $button = $(event.target).parent().find('.AknButton--apply');

    this.setAction(action);
    $button.removeClass('AknButton--apply');
    $(event.target).addClass('AknButton--apply');
  }

  /**
   * @param {string} action
   */
  protected setAction(action: string): void {
    let data = this.getFormData();

    data.jobInstanceCode = this.config.jobInstanceCode.replace('%s', action);

    this.setData(data);
  }
}

export = SuggestDataOperation;
