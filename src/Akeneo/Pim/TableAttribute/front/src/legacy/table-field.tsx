import React from 'react';
import * as ReactDOM from 'react-dom';
import {TableConfiguration} from '../models/TableConfiguration';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {TableValue} from '../models/TableValue';
import {TableFieldApp} from '../product/TableFieldApp';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
// eslint-disable-next-line @typescript-eslint/no-var-requires
const Field = require('pim/field');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const mediator = require('oro/mediator');

export type CopyContext = {scope: ChannelCode | null; locale: LocaleCode | null; data: TableValue};

export type TemplateContext = {
  type: 'akeneo-table-field';
  editMode: 'view' | 'edit';
  fieldId: string;
  label: string;
  locale: LocaleCode | null;
  scope: ChannelCode | null;
  context: {
    scopeLabel: string;
    optional: boolean;
    removable: boolean;
    root: any;
  };
  attribute: {
    code: string;
    table_configuration: TableConfiguration;
  };
  value: {
    data: TableValue;
  };
};

export type Violations = {path: string; attribute: string; locale: LocaleCode | null; scope: ChannelCode | null};

class TableField extends (Field as {new (config: any): any}) {
  private violations: Violations[] = [];
  private selected: boolean;

  constructor(config: any) {
    super(config);

    this.violations = [];
    this.copyContext = undefined;
    this.selected = false;
    this.fieldType = 'akeneo-table-field';
  }

  setFilteredViolations(event: {response: {values: Violations[]}}, attributeCode: string) {
    this.violations = event.response.values.filter(violation => {
      return violation.attribute === attributeCode;
    });
  }

  render() {
    ReactDOM.unmountComponentAtNode(this.el);

    this.setEditable(!this.locked);
    this.setValid(true);
    this.elements = {};
    const promises: Promise<any>[] = [];
    mediator.trigger('pim_enrich:form:field:extension:add', {field: this, promises: promises});

    if (this.attribute.guidelines[this.context.guidelinesLocale]) {
      this.addElement(
        'footer',
        'guidelines',
        this.guidelinesTemplate({
          guidelines: this.attribute.guidelines[this.context.guidelinesLocale],
        })
      );
    }

    Promise.all(promises).then(() => {
      this.getTemplateContext().then((templateContext: TemplateContext) => {
        this.listenTo(templateContext.context.root, 'pim_enrich:form:entity:bad_request', (event: any) => {
          this.setFilteredViolations(event, templateContext.attribute.code);
        });

        this.renderInput(templateContext, false, undefined);
      });
    });

    return this;
  }

  renderCopyInput(value: CopyContext) {
    const copyContext = {...value};
    return new Promise(resolve => {
      this.getTemplateContext().then((templateContext: TemplateContext) => {
        this.renderInput(templateContext, false, copyContext);
        resolve('');
      });
    });
  }

  renderInput(templateContext: TemplateContext, isCopying: boolean, copyContext?: CopyContext) {
    const handleChange = (value: TableValue) => {
      this.setCurrentValue(value);
    };

    const handleCopyCheckboxChange = (checked: boolean) => {
      this.selected = checked;
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <TableFieldApp
            {...templateContext}
            onChange={handleChange}
            elements={isCopying ? [] : this.elements}
            copyContext={copyContext}
            violations={this.violations}
            onCopyCheckboxChange={handleCopyCheckboxChange}
            copyCheckboxChecked={this.selected}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
  }
}

module.exports = TableField;
