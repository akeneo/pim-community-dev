import BaseView = require('pimui/js/view/base');
import {ValidationError, formatParameters, filterErrors} from '@akeneo-pim-community/shared';
import {JobAutomationForm, JobAutomationFormProps, Automation} from '@akeneo-pim-enterprise/job-automation';
const userContext = require('pim/user-context');

type JobAutomationFormControllerConfig = {tabCode?: string};

class JobAutomationFormController extends BaseView {
  public config: JobAutomationFormControllerConfig;
  private automationValidationErrors: ValidationError[] = [];
  private scheduledValidationErrors: ValidationError[] = [];

  constructor(options: {config: JobAutomationFormControllerConfig}) {
    super(options);

    this.config = options.config;
  }

  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
      this.setValidationErrors([], []);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const automationErrors = formatParameters(filterErrors(event.response.normalized_errors, '[automation]'));
      const scheduledErrors = formatParameters(filterErrors(event.response.normalized_errors, '[scheduled]'));
      this.setValidationErrors(automationErrors, scheduledErrors);

      const errors = [...automationErrors, ...scheduledErrors];
      if (errors.length > 0) {
        this.getRoot().trigger('pim_enrich:form:form-tabs:add-errors', {
          tabCode: this.getTabCode(),
          errors,
        });
      }
    });

    return BaseView.prototype.configure.apply(this);
  }

  setValidationErrors(automationValidationErrors: ValidationError[], scheduledValidationErrors: ValidationError[]) {
    this.automationValidationErrors = automationValidationErrors;
    this.scheduledValidationErrors = scheduledValidationErrors;
    this.render();
  }

  getTabCode(): string {
    return this.config.tabCode ? this.config.tabCode : this.code;
  }

  setScheduled(scheduled: boolean): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      scheduled,
    });
    this.render();
  }

  setAutomationConfiguration(automation: Automation): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      automation,
    });
    this.render();
  }

  getDefaultAutomation(): Automation {
    return {
      cron_expression: '0 0 * * *',
      running_user_groups: userContext.get('visible_group_ids') ?? [],
      notification_user_groups: [],
      notification_users: [],
    };
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: JobAutomationFormProps = {
      scheduled: formData.scheduled ?? false,
      automation: formData.automation ?? this.getDefaultAutomation(),
      automationValidationErrors: this.automationValidationErrors,
      scheduledValidationErrors: this.scheduledValidationErrors,
      onScheduledChange: this.setScheduled.bind(this),
      onAutomationChange: this.setAutomationConfiguration.bind(this),
    };

    this.renderReact<JobAutomationFormProps>(JobAutomationForm, props, this.el);

    return this;
  }
}

export = JobAutomationFormController;
