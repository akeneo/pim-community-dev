import BaseView = require('pimui/js/view/base');
import {ColumnsTab, ColumnsTabProps} from '@akeneo-pim-enterprise/tailored-export';
import {ValidationError} from '@akeneo-pim-community/shared';

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
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.tabTitle),
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => this.setValidationErrors([]));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event =>
      this.setValidationErrors(event.response.normalized_errors)
    );

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: ColumnsTabProps = {
      columnsConfiguration: formData.configuration.columns,
      onColumnsConfigurationChange: columnsConfiguration => {
        this.setData({...formData, configuration: {...formData.configuration, columns: columnsConfiguration}});
        this.render();
      },
      validationErrors: this.validationErrors,
    };

    this.renderReact<ColumnsTabProps>(ColumnsTab, props, this.el);

    return this;
  }
}

export = ColumnView;
