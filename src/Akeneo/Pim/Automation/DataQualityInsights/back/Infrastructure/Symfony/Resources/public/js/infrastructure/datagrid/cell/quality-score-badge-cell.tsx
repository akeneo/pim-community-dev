import ReactDOM from 'react-dom';
import React from 'react';
import {QualityScore} from '@akeneo-pim-community/data-quality-insights/src/application/component/QualityScore';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const StringCell = require('oro/datagrid/string-cell');

class QualityScoreBadgeCell extends StringCell {
  render() {
    if (this.model.attributes.document_type !== 'product') {
      // PLG-720 -> PLG-761 : making sure we wont have quality score displayed for product-model before it's ready
      // @TODO [PLG-750] remove this test
      return this;
    }

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

export = QualityScoreBadgeCell;
