import React from 'react';
import * as ReactDOM from 'react-dom';
import { TableInputApp } from './TableInputApp';
import { TableConfiguration } from '../models/TableConfiguration';
import { DependenciesProvider } from '@akeneo-pim-community/legacy-bridge';
import { ThemeProvider } from 'styled-components';
import { pimTheme } from 'akeneo-design-system';
const Field = require('pim/field');

class TableField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-table-field';
  }

  renderInput(templateContext: any) {
    const valueData = templateContext.value.data;
    const tableConfiguration = templateContext.attribute.table_configuration as TableConfiguration;
    const container = document.createElement('div');
    const handleChange = (value: any) => {
      this.setCurrentValue(value);
    }

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <TableInputApp valueData={valueData} tableConfiguration={tableConfiguration} onChange={handleChange}/>
        </ThemeProvider>
      </DependenciesProvider>,
      container
    );
    return container;
  }
}

module.exports = TableField;
