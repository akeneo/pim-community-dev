import ReactDOM from 'react-dom';
import React from 'react';
import Rate from '@akeneo-pim-community/data-quality-insights/src/application/component/Rate';

const StringCell = require('oro/datagrid/string-cell');

class RateBadgeCell extends StringCell {
  render() {
    const productRate = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    let value = null;
    if (productRate !== 'N/A') {
      value = productRate;
    }

    ReactDOM.render(<Rate value={value} />, this.el);

    this.$el.addClass('AknDataQualityInsightsGrid-axis-rate');

    return this;
  }
}

export = RateBadgeCell;
