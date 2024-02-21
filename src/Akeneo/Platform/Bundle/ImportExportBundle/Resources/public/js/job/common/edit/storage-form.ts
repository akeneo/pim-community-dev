import BaseView = require('pimui/js/view/base');
import {ValidationError, formatParameters, filterErrors} from '@akeneo-pim-community/shared';
import {StorageForm, StorageFormProps, Storage} from '@akeneo-pim-community/import-export';

type StorageFormControllerConfig = {tabCode?: string; jobType: 'import' | 'export'; fileExtension: string};

class StorageFormController extends BaseView {
  public config: StorageFormControllerConfig;
  private validationErrors: ValidationError[] = [];

  constructor(options: {config: StorageFormControllerConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
      this.setValidationErrors([]);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const errors = formatParameters(filterErrors(event.response.normalized_errors, '[storage]'));
      this.setValidationErrors(errors);

      if (errors.length > 0) {
        this.getRoot().trigger('pim_enrich:form:form-tabs:add-errors', {
          tabCode: this.getTabCode(),
          errors,
        });
      }
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    this.render();
  }

  getTabCode(): string {
    return this.config.tabCode ? this.config.tabCode : this.code;
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
      file_path: '',
    };
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: StorageFormProps = {
      jobInstanceCode: formData.code,
      storage: formData.configuration.storage ?? this.getDefaultStorage(),
      jobType: this.config.jobType,
      fileExtension: this.config.fileExtension,
      validationErrors: this.validationErrors,
      onStorageChange: this.setStorage.bind(this),
    };

    this.renderReact<StorageFormProps>(StorageForm, props, this.el);

    return this;
  }
}

export = StorageFormController;
