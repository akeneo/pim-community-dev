import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {TableOptionsApp} from './TableOptionsApp';
import {TableConfiguration} from '../models/TableConfiguration';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {TEMPLATES} from '../models/Template';
import {AttributeType} from '../models/Attribute';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');

type TableOptionsTabConfig = {
  label: string;
  activeForTypes: AttributeType[];
};

class TableOptionsTab extends (BaseView as {new (options: {config: TableOptionsTabConfig}): any}) {
  private config: TableOptionsTabConfig;

  initialize(options: {config: TableOptionsTabConfig}): void {
    this.config = options.config;

    BaseView.prototype.initialize.apply(this, options);
  }

  configure(): JQueryPromise<any> {
    if (this.isActive()) {
      this.trigger('tab:register', {
        code: this.code,
        label: translate(this.config.label),
      });
    }

    return super.configure();
  }

  handleChange(tableConfiguration: TableConfiguration): void {
    const data = this.getFormData();
    data.table_configuration = tableConfiguration;
    this.setData({...data});
  }

  getQueryParam(paramName: string): any {
    const urlString = window.location.href;
    const index = urlString.indexOf('?');
    if (index < 0) {
      return null;
    }
    const params = new URLSearchParams(urlString.substring(index + 1));

    return params.get(paramName);
  }

  render(): any {
    if (!this.isActive()) {
      return;
    }

    let initialTableConfiguration = this.getFormData().table_configuration;
    if (typeof initialTableConfiguration === 'undefined') {
      initialTableConfiguration = [];
      const tableTemplate = this.getQueryParam('table_template');
      if (tableTemplate) {
        const template = TEMPLATES.find(template => template.code === tableTemplate);
        if (template) {
          initialTableConfiguration = template.tableConfiguration;
        }
      }
    }

    ReactDOM.render(
      <DependenciesProvider>
        <TableOptionsApp
          initialTableConfiguration={initialTableConfiguration}
          onChange={this.handleChange.bind(this)}
        />
      </DependenciesProvider>,
      this.el
    );
    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private isActive() {
    return this.config.activeForTypes.includes(this.getRoot().getType());
  }
}

export = TableOptionsTab;
