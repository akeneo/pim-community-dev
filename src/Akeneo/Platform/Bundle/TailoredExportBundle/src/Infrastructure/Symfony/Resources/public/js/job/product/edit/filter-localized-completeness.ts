import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {Channel, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AssociationType, Attribute, CompletenessFilter, FetcherContext} from '@akeneo-pim-enterprise/tailored-export';
const __ = require('oro/translator');
const BaseFilter = require('pim/filter/filter');
const BaseCompletenessFilter = require('pim/filter/product/completeness');
const fetcherRegistry = require('pim/fetcher-registry');

class FilterLocalizedCompleteness extends BaseCompletenessFilter {
  private validationErrors: ValidationError[] = [];

  /**
   * {@inheritdoc}
   */
  getTemplateContext() {
    return {
      label: __('pim_enrich.export.product.filter.completeness.title'),
      removable: this.isRemovable(),
      editable: this.isEditable(),
    };
  }

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
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:pre_save', () => this.setValidationErrors([]));
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:bad_request', (event: any) =>
      this.setValidationErrors(event.response.normalized_errors)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    if (this.$('.completeness-filter-container').length > 0) {
      this.postRender();
    }
  }

  /**
   * Override to prevent the modal handler being called on the base Category filter
   */
  openSelector() {}

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput() {
    return '<div class="completeness-filter-container" style="width: 100%"></div>';
  }

  isEmpty() {
    return false;
  }

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
          React.createElement(
            FetcherContext.Provider,
            {
              value: {
                attribute: {
                  fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('attribute')
                        .fetchByIdentifiers(identifiers)
                        .then(resolve)
                    );
                  },
                },
                channel: {
                  fetchAll: (): Promise<Channel[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('channel')
                        .fetchAll()
                        .then(resolve)
                    );
                  },
                },
                associationType: {
                  fetchByCodes: (codes: string[]): Promise<AssociationType[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('association-type')
                        .fetchByIdentifiers(codes)
                        .then(resolve)
                    );
                  },
                },
              },
            },
            React.createElement(CompletenessFilter, {
              availableOperators: this.config.operators,
              filter: this.getFormData(),
              onChange: newFilter => {
                this.getRoot().model.clear({silent: true});
                this.setData(newFilter);
                this.render();
              },
              // TODO: Find a way to get rid of the [2] part below
              validationErrors: filterErrors(this.validationErrors, '[filters][data][2]'),
            })
          )
        )
      ),
      this.$('.completeness-filter-container')[0]
    );
  }
}

export = FilterLocalizedCompleteness;
