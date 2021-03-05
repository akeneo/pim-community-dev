import ReactDOM from 'react-dom';
import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Scoring} from 'akeneo-design-system';

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    const productQualityScore: string = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <Scoring score={productQualityScore} />
      </ThemeProvider>,
      this.el
    );
    return this;
  }
}

export = QualityScoreBadgeCell;
