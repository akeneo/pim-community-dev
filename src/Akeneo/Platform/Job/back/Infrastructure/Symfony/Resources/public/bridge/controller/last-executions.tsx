import BaseView = require('pimui/js/view/base');
import {
  JobInstancePage,
  JobInstancePageProps,
} from '@akeneo-pim-community/process-tracker/lib/components/JobExecutionList/JobInstancePage';

class ColumnView extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const {code, type} = this.getFormData();

    this.renderReact<JobInstancePageProps>(JobInstancePage, {code, type}, this.el);

    return this;
  }
}

export = ColumnView;
