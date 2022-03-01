import ReactDOM from 'react-dom';
import React from 'react';
import {QualityScore} from '@akeneo-pim-community/data-quality-insights/src/application/component/QualityScore';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    const qualityScoreProps = {
      score: this.formatter.fromRaw(this.model.get(this.column.get('name'))),
      stacked: this.model.attributes.document_type === 'product_model',
    };

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <QualityScore {...qualityScoreProps} />
      </ThemeProvider>,
      this.el
    );
    return this;
  }
}

export = QualityScoreBadgeCell;
