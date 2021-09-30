// eslint-disable-next-line @typescript-eslint/no-var-requires
import {FilterValuesMapping} from "../datagrid";

const AbstractFilter = require('pim/filter/attribute/attribute');
import React from 'react';
import ReactDOM from 'react-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Attribute, TableAttribute} from "../models";
import {ProductExportBuilderFilter} from "../datagrid/ProductExportBuilderFilter";

type TemplateContext = {
  attribute: Attribute;
  editable: boolean;
  label: string;
  removable: boolean;
}

class ProductExportTableFilter extends AbstractFilter {
  renderInput(templateContext: TemplateContext): any {
    const filterValuesMapping = __moduleConfig.filter_values as FilterValuesMapping;

    const {attribute, editable, label, removable} = templateContext;
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ProductExportBuilderFilter
            attribute={attribute as TableAttribute}
            editable={editable}
            label={label}
            removable={removable}
            filterValuesMapping={filterValuesMapping}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }

  remove(): any {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = ProductExportTableFilter;
