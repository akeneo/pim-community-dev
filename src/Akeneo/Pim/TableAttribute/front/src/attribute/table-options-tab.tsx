import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {TableOptionsApp} from './TableOptionsApp';
import {TableConfiguration} from '../models/TableConfiguration';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');

type TableOptionsTabConfig = {
  label: string;
}

class TableOptionsTab extends BaseView {
  private config: TableOptionsTabConfig;

  initialize(config: { config: TableOptionsTabConfig }): void {
    this.config = config.config;
    BaseView.prototype.initialize.apply(this, config);
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

  render(): any {
    if (!this.isActive()) {
      return;
    }
    const initialTableConfiguration = this.getFormData().table_configuration;

    ReactDOM.render(
      <TableOptionsApp initialTableConfiguration={initialTableConfiguration} onChange={this.handleChange.bind(this)} />,
      this.el
    );
    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private isActive() {
    return this.config.activeForTypes.includes((this.getRoot() as any).getType());
  }
}

export = TableOptionsTab;
