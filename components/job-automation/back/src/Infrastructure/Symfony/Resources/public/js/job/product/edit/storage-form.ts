import BaseView = require('pimui/js/view/base');
import {StorageForm, StorageFormProps, Storage} from '@akeneo-pim-enterprise/job-automation';

class StorageFormController extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  setStorage(storage: Storage): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      configuration: {
        ...formData.configuration,
        storage,
      },
    });
    this.render();
  }

  getDefaultStorage(): Storage {
    return {
      type: 'none',
    };
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: StorageFormProps = {
      storage: formData.configuration.storage ?? this.getDefaultStorage(),
      onChange: this.setStorage.bind(this),
    };

    this.renderReact<StorageFormProps>(StorageForm, props, this.el);

    return this;
  }
}

export = StorageFormController;
