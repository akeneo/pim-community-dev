import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {TableStructureApp} from '../attribute/TableStructureApp';
import {TableConfiguration} from '../models/TableConfiguration';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {TemplateVariation, TEMPLATES} from '../models/Template';
import {Attribute, AttributeType} from '../models/Attribute';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');

type TableStructureTabConfig = {
  label: string;
  activeForTypes: AttributeType[];
};

type Violation = {global: boolean; message: string; path: string};

class TableStructureTab extends (BaseView as {new (options: {config: TableStructureTabConfig}): any}) {
  private config: TableStructureTabConfig;
  private violations: Violation[];
  private savedColumnCodes: string[] | undefined;

  initialize(options: {config: TableStructureTabConfig}): void {
    this.config = options.config;
    this.violations = [];
    this.savedColumnCodes = undefined;
    BaseView.prototype.initialize.apply(this, options);
  }

  configure(): JQueryPromise<any> {
    if (this.isActive()) {
      this.trigger('tab:register', {
        code: this.code,
        label: translate(this.config.label),
      });

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onBadRequest.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.removeErrors.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', this.updateSavedColumns.bind(this));
    }

    return super.configure();
  }

  removeErrors(): void {
    if (this.violations.length) {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-error', this.code);
    }
    this.violations = [];
  }

  resetSavedColumns(): void {
    this.savedColumnCodes = undefined;
  }

  onBadRequest(event: {response: Violation[]}): void {
    this.violations = event.response;

    /** Possible paths:
     * - 'table_configuration' (error on column count)
     * - 'raw_table_configuration' (error on duplicate code)
     * - 'raw_table_configuration[2][validations]' (error on a validation field)
     */
    const isATableConfigurationViolation: (path: string) => boolean = path => {
      if (path === 'table_configuration') return true;
      if (path === 'raw_table_configuration') return true;
      if (/^raw_table_configuration/.exec(path)) return true;

      return false;
    };

    if (event.response.some(violation => isATableConfigurationViolation(violation.path))) {
      this.getRoot().trigger('pim_enrich:form:form-tabs:add-error', this.code);
      this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);
    }
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

    let initialTableConfiguration = this.getFormData().table_configuration as TableConfiguration | undefined;
    if (typeof this.savedColumnCodes === 'undefined') {
      this.savedColumnCodes = (initialTableConfiguration || []).map(columnDefinition => columnDefinition.code);
    }
    if (typeof initialTableConfiguration === 'undefined') {
      initialTableConfiguration = [];
      const tableTemplate = this.getQueryParam('template_variation');
      if (tableTemplate) {
        const template = ([] as TemplateVariation[])
          .concat(...TEMPLATES.map(template => template.template_variations))
          .find(template => template.code === tableTemplate);
        if (template) {
          initialTableConfiguration = template.tableConfiguration;
          this.handleChange(initialTableConfiguration);
        } else {
          console.error(`Unable to find template ${tableTemplate}`);
        }
      }
    }

    const attribute: Attribute = this.getFormData();

    ReactDOM.render(
      <DependenciesProvider>
        <TableStructureApp
          attribute={attribute}
          initialTableConfiguration={initialTableConfiguration}
          onChange={this.handleChange.bind(this)}
          savedColumnCodes={this.savedColumnCodes}
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

export = TableStructureTab;
