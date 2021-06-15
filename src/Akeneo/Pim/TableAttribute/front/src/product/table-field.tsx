import React from 'react';
import * as ReactDOM from 'react-dom';
import { TableInputApp } from './TableInputApp';
import { TableConfiguration } from '../models/TableConfiguration';
import { DependenciesProvider } from '@akeneo-pim-community/legacy-bridge';
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
    ReactDOM.render(
      <DependenciesProvider>
        <TableInputApp valueData={valueData} tableConfiguration={tableConfiguration}/>
      </DependenciesProvider>,
      container
    );
    return container;
  }
}

module.exports = TableField;
