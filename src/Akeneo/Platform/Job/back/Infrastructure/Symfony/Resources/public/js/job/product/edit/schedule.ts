import BaseView = require('pimui/js/view/base');
import {ScheduleTab, ScheduleTabProps} from './ScheduleTab';

class ScheduleView extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.trigger('tab:register', {
      code: this.getTabCode(),
      label: this.config.tabTitle,
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  getTabCode(): string {
    return this.config.tabCode ? this.config.tabCode : this.code;
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const props: ScheduleTabProps = {
      // We need to retrieve the current job instance code
      jobInstanceCode: 'csv_product_export',
    };

    this.renderReact(ScheduleTab, props, this.el)

    return this;
  }
}

export = ScheduleView;
