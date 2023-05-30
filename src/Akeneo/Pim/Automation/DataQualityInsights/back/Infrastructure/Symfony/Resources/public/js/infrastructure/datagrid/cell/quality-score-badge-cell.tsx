import ReactDOM from 'react-dom';
import React from 'react';
import {QualityScoreValue, QualityScore, QualityScorePending} from '@akeneo-pim-community/data-quality-insights';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    const score: QualityScoreValue | 'N/A' | null = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    function isPending(score: QualityScoreValue | 'N/A' | null): score is 'N/A' | null {
      return null === score || 'N/A' == score;
    }

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          {isPending(score) ? (
            <QualityScorePending />
          ) : (
            <QualityScore score={score} stacked={this.model.attributes.document_type === 'product_model'} />
          )}
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }
}

export = QualityScoreBadgeCell;
