import BaseView = require('pimui/js/view/base');
import {GlobalSettings} from '@akeneo-pim-enterprise/tailored-import';
import {formatParameters, getErrorsForPath, ValidationError} from '@akeneo-pim-community/shared';
import {GlobalSettingsTab, GlobalSettingsTabProps} from '@akeneo-pim-enterprise/tailored-import';

const __ = require('oro/translator');

class GlobalSettingsView extends BaseView {
  public config: any;
  private validationErrors: ValidationError[] = [];

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.trigger('tab:register', {
      code: this.getTabCode(),
      label: __(this.config.tabTitle),
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
      this.setValidationErrors([]);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const errors = formatParameters(getErrorsForPath(event.response.normalized_errors, '[error_action]'));
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

  setGlobalSettingConfiguration(globalSettings: GlobalSettings): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      configuration: {...formData.configuration, ...globalSettings},
    });
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();
    this.renderReact<GlobalSettingsTabProps>(
      GlobalSettingsTab,
      {
        globalSettings: {
          error_action: formData.configuration.error_action,
        },
        validationErrors: this.validationErrors,
        onGlobalSettingsChange: this.setGlobalSettingConfiguration.bind(this),
      },
      this.el
    );

    return this;
  }
}

export = GlobalSettingsView;
