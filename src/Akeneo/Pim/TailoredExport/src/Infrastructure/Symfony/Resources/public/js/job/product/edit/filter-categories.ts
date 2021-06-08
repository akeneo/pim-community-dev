import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CategoryFilter} from '@akeneo-pim-enterprise/tailored-export';
const BaseCategoryFilter = require('pim/filter/product/category');
const __ = require('oro/translator');

class FilterCategories extends BaseCategoryFilter {
  /**
   * {@inheritdoc}
   */
  getTemplateContext() {
    return {
      label: __('pim_enrich.export.product.filter.category.title'),
      removable: this.isRemovable(),
      editable: this.isEditable(),
    };
  }

  /**
   * Override to prevent the modal handler being called on the base Category filter
   */
  openSelector() {}

  /**
   * {@inheritdoc}
   */
  postRender() {
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(
          DependenciesProvider,
          null,
          React.createElement(CategoryFilter, {
            initialCategorySelection: this.getValue(),
            onCategorySelection: (categoriesSelected: string[]) => {
              this.setData({
                field: this.getField(),
                operator: categoriesSelected.length === 0 ? 'NOT IN' : 'IN',
                value: categoriesSelected,
              });

              this.render();
            },
          })
        )
      ),
      this.$('.AknTextField')[0]
    );
  }
}

export = FilterCategories;
