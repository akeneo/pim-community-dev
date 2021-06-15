import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CompletenessFilter} from '@akeneo-pim-enterprise/tailored-export';
const BaseFilter = require('pim/filter/filter');
const __ = require('oro/translator');

class FilterLocalizedCompleteness extends BaseFilter {
  /**
   * {@inheritdoc}
   */
  initialize(config: any) {
    this.config = config?.config;
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(
        this.getRoot(),
        'pim_enrich:form:entity:pre_update',
        (data: unknown) => {
          _.defaults(data, {field: this.getCode(), operator: _.first(this.config.operators), value: 100});
        }
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
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
          React.createElement(CompletenessFilter, {
            operator: this.getValue(),
            locales: this.getValue(),
            onOperatorChange: (categoriesSelected: string[]) => {
              this.setData({
                field: this.getField(),
                operator: categoriesSelected.length === 0 ? 'NOT IN' : 'IN',
                value: categoriesSelected,
              });

              this.render();
            },
              onlocaleChange: () => {

              }
          })
        )
      ),
      this.$('.AknTextField')[0]
    );
  }

  /**
   * {@inheritdoc}
   */
  isEmpty() {
    return this.config.neverEmpty ? false : 'ALL' === this.getOperator();
  }
}

export = FilterLocalizedCompleteness;
