const BaseView = require('pimui/js/view/base');
import {Dummy} from '@akeneo-pim-enterprise/tailored-import';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';
const __ = require('oro/translator');

class ColumnView extends BaseView {
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
      this.setValidationErrors(event.response.normalized_errors);

      const errors = filterErrors(this.validationErrors, '[import-structure]');
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

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    this.renderReact(Dummy, {}, this.el);

    return this;
  }
}

export = ColumnView;
