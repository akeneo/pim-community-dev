import BaseView = require('pimui/js/view/base');
import {RemoteStorageTab, RemoteStorageTabProps} from './RemoteStorageTab';

const mediator = require('oro/mediator');

class FileTransferView extends BaseView {
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
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.handlePreSave);

    return BaseView.prototype.configure.apply(this, arguments);
  }

  handlePreSave() {
    mediator.trigger('job_server_credentials:pre_save');
  }

  getTabCode(): string {
    return this.config.tabCode ? this.config.tabCode : this.code;
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const {code} = this.getFormData(); // `job_name` also works

    const props: RemoteStorageTabProps = {
      jobInstanceCode: code,
    };

    this.renderReact(RemoteStorageTab, props, this.el);

    return this;
  }
}

export = FileTransferView;
