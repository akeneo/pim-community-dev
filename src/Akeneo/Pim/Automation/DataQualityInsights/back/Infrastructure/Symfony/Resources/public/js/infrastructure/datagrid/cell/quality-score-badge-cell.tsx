import ReactDOM from 'react-dom';
import React from 'react';
import {QualityScore} from '@akeneo-pim-community/data-quality-insights/src/application/component/QualityScore';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
  QualityScorePending
} from "@akeneo-pim-community/data-quality-insights/src/application/component/QualityScorePending";

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    const qualityScoreProps = {
      score: this.formatter.fromRaw(this.model.get(this.column.get('name'))),
      stacked: this.model.attributes.document_type === 'product_model',
    };

    const isPending = (qualityScoreProps.score === 'N/A' || qualityScoreProps.score === null)

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          {isPending ? <QualityScorePending/> : <QualityScore {...qualityScoreProps} />}
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }
}

export = QualityScoreBadgeCell;
