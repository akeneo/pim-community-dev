// eslint-disable-next-line @typescript-eslint/no-var-requires
const AbstractFilter = require('pim/filter/attribute/attribute');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const UserContext = require('pim/user-context');
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
import {LocaleCodeContext} from '../contexts';

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
    if (
      typeof initialDataFilter?.value !== 'undefined' &&
      typeof initialDataFilter?.value?.row === 'undefined' &&
      typeof initialDataFilter?.operator !== 'undefined'
    ) {
      initialDataFilter.value.row = null;
    }

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <LocaleCodeContext.Provider value={{localeCode: UserContext.get('catalogLocale')}}>
            <ProductExportBuilderFilter
              attribute={attribute as TableAttribute}
              onChange={handleChange}
              initialDataFilter={initialDataFilter}
            />
          </LocaleCodeContext.Provider>
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
