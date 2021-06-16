import ReactDOM from 'react-dom';
import React from 'react';
import {QualityScore} from '@akeneo-pim-community/data-quality-insights/src/application/component/QualityScore';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    const productQualityScore: string = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <QualityScore score={productQualityScore} />
      </ThemeProvider>,
      this.el
    );
    return this;
  }
}

export default QualityScoreBadgeCell;
