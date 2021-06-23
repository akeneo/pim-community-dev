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
  };
  attribute: {
    code: string;
    table_configuration: TableConfiguration;
  };
  value: {
    data: TableValue;
  };
};

class TableField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-table-field';
  }

  render() {
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
        const handleChange = (value: TableValue) => {
          this.setCurrentValue(value);
        };

        ReactDOM.render(
          <DependenciesProvider>
            <ThemeProvider theme={pimTheme}>
              <TableFieldApp {...templateContext} onChange={handleChange} elements={this.elements} />
            </ThemeProvider>
          </DependenciesProvider>,
          this.el
        );
      });
    });

    return this;
  }
}

module.exports = TableField;
