import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
  AssetFamily,
  AssociationType,
  Attribute,
  FetcherContext,
  MeasurementFamily,
  QualityScoreFilter,
  Filter
} from '@akeneo-pim-enterprise/syndication';
import {Channel, filterErrors, ValidationError} from '@akeneo-pim-community/shared';
const _ = require('underscore');
const BaseFilter = require('pim/filter/filter');
const mediator = require('oro/mediator');
const fetcherRegistry = require('pim/fetcher-registry');

let assetFamilyFetcher = {
  fetch: (_identifier: string) => {
    return Promise.resolve({assetFamily: {attributes: [], attributeAsMainMedia: '', identifier: ''}});
  }
};

class FilterQualityScore extends BaseFilter {
  private validationErrors: ValidationError[] = [];

  /**
   * {@inheritdoc}
   */
  initialize(config: any) {
    this.config = config?.config;
  }

  /**
   * {@inheritdoc}
   */
  isEmpty() {
    return _.isEmpty(this.getValue());
  }

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput() {
    return '<div class="quality-score-filter-container" style="width: 100%;margin-top: 60px;margin-bottom: 60px;"></div>';
  }

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:pre_save', () => this.setValidationErrors([]));
    this.listenTo(this.parentForm.getRoot(), 'pim_enrich:form:entity:bad_request', (event: any) =>
      this.setValidationErrors(event.response.normalized_errors)
    );
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', (data: any) => {
      _.defaults(data, {field: this.getCode(), operator: null, value: []});
    });

    return BaseFilter.prototype.configure.apply(this, arguments);
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
                measurementFamily: {
                  fetchByCode: (code: string): Promise<MeasurementFamily | undefined> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('measure')
                        .fetch(code)
                        .then(resolve)
                    );
                  },
                  fetchAll: (): Promise<MeasurementFamily[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('measure')
                        .fetchAll()
                        .then(resolve)
                    );
                  },
                },
                assetFamily: {
                  fetchByIdentifier: async (identifier: string): Promise<AssetFamily | undefined> => {
                    const {assetFamily} = await assetFamilyFetcher.fetch(identifier);
                    return {
                      identifier: assetFamily.identifier,
                      attribute_as_main_media: assetFamily.attributeAsMainMedia,
                      attributes: assetFamily.attributes,
                    };
                  },
                },
              },
            },
            React.createElement(QualityScoreFilter, {
              availableOperators: this.config.operators,
              filter: this.getFormData(),
              onChange: (newFilter: Filter) => {
                this.getRoot().model.clear({silent: true});
                this.setData(newFilter);
                this.render();
              },
              validationErrors: filterErrors(this.validationErrors, '[filters][data][quality_score_multi_locales]'),
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
