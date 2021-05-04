import React from 'react';
import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {ColumnsTab} from '@akeneo-pim-enterprise/tailored-export';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
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

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', validationError => this.setValidationErrors(validationError.response.normalized_errors));

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(response: ValidationError[]) {
    this.validationErrors = response;
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <ColumnsTab
            columnsConfiguration={formData.configuration.columns}
            onColumnsConfigurationChange={columnsConfiguration => {
              this.setData({...formData, configuration: {...formData.configuration, columns: columnsConfiguration}});
              this.render();
            }}
            validationErrors={this.validationErrors}
          />
        </DependenciesProvider>
      </ThemeProvider>,
      this.el
    );

    this.el.style = 'height: calc(100vh - 278px)';
    return this;
  }
}

export = ColumnView;
