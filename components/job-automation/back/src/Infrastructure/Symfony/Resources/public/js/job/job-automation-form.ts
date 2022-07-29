import BaseView = require('pimui/js/view/base');
import {ValidationError, formatParameters, filterErrors} from '@akeneo-pim-community/shared';
import {JobAutomationForm, JobAutomationFormProps, Automation} from '@akeneo-pim-enterprise/job-automation';
const userContext = require('pim/user-context');

type JobAutomationFormControllerConfig = {tabCode?: string};

class JobAutomationFormController extends BaseView {
  public config: JobAutomationFormControllerConfig;
  private validationErrors: ValidationError[] = [];

  constructor(options: {config: JobAutomationFormControllerConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
      this.setValidationErrors([]);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const errors = formatParameters(filterErrors(event.response.normalized_errors, '[automation]'));
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

  setAutomation(automation: Automation): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      configuration: {
        ...formData.configuration,
        automation,
      },
    });
    this.render();
  }

  getDefaultAutomation(): Automation {
    return {
      is_enabled: false,
      cron_expression: '0 0 * * *',
      running_user_groups: userContext.get('groups'),
    };
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: JobAutomationFormProps = {
      automation: formData.configuration.automation ?? this.getDefaultAutomation(),
      validationErrors: this.validationErrors,
      onAutomationChange: this.setAutomation.bind(this),
    };

    this.renderReact<JobAutomationFormProps>(JobAutomationForm, props, this.el);

    return this;
  }
}

export = JobAutomationFormController;
