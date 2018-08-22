import * as _ from 'underscore';

const __ = require('oro/translator');
const Operation = require('pim/mass-edit-form/product/operation');
const template = require('pimee/template/product/mass-edit/suggest-data');

interface SuggestDataOperationConfig {
  title: string;
  label: string;
  labelCount: string;
  description: string;
  code: string;
  jobInstanceCode: string;
  warning: string;
  icon: string;
}

/**
 * Mass suggest data operation
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestDataOperation extends Operation {
  readonly template: any = _.template(template);
  readonly config: SuggestDataOperationConfig = {
    title: '',
    label: '',
    labelCount: '',
    description: '',
    code: '',
    jobInstanceCode: '',
    warning: '',
    icon: '',
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: SuggestDataOperationConfig }) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  public render() {
    this.$el.html(this.template({
      warning: __(this.config.warning, {itemsCount: this.getFormData().itemsCount})
    }));

    return this;
  }
}

export = SuggestDataOperation;
