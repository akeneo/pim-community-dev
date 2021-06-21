import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {LocaleCode, Channel, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Attribute, CompletenessFilter, FetcherContext, Operator} from '@akeneo-pim-enterprise/tailored-export';
const _ = require('underscore');
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
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', (data: unknown) => {
      _.defaults(data, {
        field: this.getCode(),
        operator: _.first(this.config.operators),
        value: 100,
        context: {locales: []},
      });
    });

    return BaseFilter.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    this.postRender();
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
                      fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(identifiers).then(resolve)
                    );
                  },
                },
                channel: {
                  fetchAll: (): Promise<Channel[]> => {
                    return new Promise(resolve => fetcherRegistry.getFetcher('channel').fetchAll().then(resolve));
                  },
                },
              },
            },
            React.createElement(CompletenessFilter, {
              // availableOperators: this.config.operators,
              // filter: this.getFormData,
              // onFilterChange: this.setData,

              operator: this.getFormData().operator,
              locales: this.getFormData()?.context.locales ?? [],
              onOperatorChange: (operator: Operator) => {
                this.setData({
                  field: this.getField(),
                  operator: operator,
                  value: 100,
                  context: {locales: this.getFormData()?.context.locales ?? []},
                });

                this.render();
              },
              onLocalesChange: (locales: LocaleCode[]) => {
                this.setData({
                  field: this.getField(),
                  operator: this.getFormData().operator,
                  value: 100,
                  context: {locales},
                });

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
