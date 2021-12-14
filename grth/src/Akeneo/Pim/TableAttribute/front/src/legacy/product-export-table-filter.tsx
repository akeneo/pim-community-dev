// eslint-disable-next-line @typescript-eslint/no-var-requires
const AbstractFilter = require('pim/filter/attribute/attribute');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Attribute, TableAttribute} from '../models';
import {
  BackendTableProductExportFilterValue,
  PendingTableProductExportFilterValue,
  ProductExportBuilderFilter,
} from '../datagrid/ProductExportBuilderFilter';

type TemplateContext = {
  attribute: Attribute;
  editable: boolean;
  label: string;
  removable: boolean;
};

class ProductExportTableFilter extends AbstractFilter {
  private element: Element | undefined = undefined;

  private updateState(value: BackendTableProductExportFilterValue) {
    this.setData(value);
  }

  renderInput(templateContext: TemplateContext): any {
    if (this.element) {
      return this.element;
    }

    this.element = document.createElement('div');
    const {attribute} = templateContext;
    const handleChange = this.updateState.bind(this);

    const initialDataFilter = this.getFormData() as PendingTableProductExportFilterValue;

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ProductExportBuilderFilter
            attribute={attribute as TableAttribute}
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
