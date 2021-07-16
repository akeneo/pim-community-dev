import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
// import {QualityScoreFilter} from '@akeneo-pim-enterprise/tailored-export';
import {ValidationError} from '@akeneo-pim-community/shared';
const BaseQualityScoreFilter = require('pim/filter/product/quality-score');
const __ = require('oro/translator');

class QualityScoreFilter extends BaseQualityScoreFilter {
  /**
   * {@inheritdoc}
   */
  initialize(config: any) {
    this.config = config?.config;
  }

  /**
   * {@inheritdoc}
   */
  getTemplateContext() {
    return {
      label: __('pim_enrich.export.product.filter.' + this.shortname + '.title'),
      label_operator: __('pim_enrich.export.product.filter.' + this.shortname + '.operator_choice_title'),
      removable: false,
      editable: this.isEditable(),
    };
  }

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput() {
    return '<div class="quality-score-filter-container" style="width: 100%"></div>';
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
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(DependenciesProvider, null, React.createElement('div', {}, 'coucou'))
      ),
      this.$('.AknTextField')[0]
    );
  }
}

export = QualityScoreFilter;
