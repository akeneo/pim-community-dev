// eslint-disable-next-line @typescript-eslint/no-var-requires
const AbstractFilter = require('pim/filter/attribute/attribute');
import {BackendTableFilterValue} from '../datagrid/FilterSelectorList';
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Attribute, TableAttribute} from '../models';
import {ProductExportBuilderFilter} from '../datagrid/ProductExportBuilderFilter';
import {FilterValuesMapping} from '../datagrid';

type TemplateContext = {
  attribute: Attribute;
  editable: boolean;
  label: string;
  removable: boolean;
};

class ProductExportTableFilter extends AbstractFilter {
  private element: Element | undefined = undefined;

  private updateState(value: BackendTableFilterValue) {
    const data = {
      field: this.getField(),
      operator: value.operator,
      value: value.value,
      row: value.row,
      column: value.column,
    };

    this.setData(data);
  }

  renderInput(templateContext: TemplateContext): any {
    if (this.element) {
      return this.element;
    }

    this.element = document.createElement('div');
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const filterValuesMapping = __moduleConfig.filter_values as FilterValuesMapping;
    const {attribute} = templateContext;
    const handleChange = this.updateState.bind(this);

    const initialDataFilter = this.getFormData() as BackendTableFilterValue;

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ProductExportBuilderFilter
            attribute={attribute as TableAttribute}
            filterValuesMapping={filterValuesMapping}
            onChange={handleChange}
            initialDataFilter={initialDataFilter}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.element
    );

    return this.element;
  }

  remove(): any {
    if (this.element) {
      ReactDOM.unmountComponentAtNode(this.element);
      this.element = undefined;

      return super.remove();
    }
  }
}

export = ProductExportTableFilter;
