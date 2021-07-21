import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AssociationType, Attribute, FetcherContext, QualityScoreFilter} from '@akeneo-pim-enterprise/tailored-export';
import {Channel, filterErrors, ValidationError} from '@akeneo-pim-community/shared';
const BaseQualityScoreFilter = require('pim/filter/filter');
const mediator = require('oro/mediator');
const fetcherRegistry = require('pim/fetcher-registry');

class FilterQualityScore extends BaseQualityScoreFilter {
  private validationErrors: ValidationError[] = [];

  /**
   * {@inheritdoc}
   */
  initialize(config: any) {
    this.config = config?.config;
  }

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput() {
    return '<div class="quality-score-filter-container" style="width: 100%;"></div>';
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:pre_save', () => this.setValidationErrors([]));
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:bad_request', (event: any) =>
      this.setValidationErrors(event.response.normalized_errors)
    );

    return BaseQualityScoreFilter.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    if (this.$('.quality-score-filter-container').length > 0) {
      this.postRender();
    }
  }
  /**
   * {@inheritdoc}
   */
  render() {
    this.elements = {};
    this.setEditable(true);

    mediator.trigger('pim_enrich:form:filter:extension:add', {filter: this});

    this.$el.html(this.renderInput());
    this.renderElements();
    this.postRender();
    this.delegateEvents();

    return this;
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
            React.createElement(QualityScoreFilter, {
              availableOperators: this.config.operators,
              filter: this.getFormData(),
              onChange: newFilter => {
                this.setData(newFilter);
                this.render();
              },
              // TODO: Find a way to get rid of the [3] part below
              validationErrors: filterErrors(this.validationErrors, '[filters][data][3]'),
            })
          )
        )
      ),
      this.$('.quality-score-filter-container')[0]
    );
  }
  /**
   * {@inheritdoc}
   */
  updateState() {}
}

export = FilterQualityScore;
