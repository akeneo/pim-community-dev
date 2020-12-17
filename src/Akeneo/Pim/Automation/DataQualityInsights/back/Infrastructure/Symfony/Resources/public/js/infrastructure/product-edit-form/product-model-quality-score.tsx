import React from 'react';
import ReactDOM from 'react-dom';
import {QualityScoreBar} from '@akeneo-pim-community/data-quality-insights/src';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

const BaseView = require('pimui/js/view/base');

class ProductModelQualityScore extends BaseView {
  public render() {
    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <QualityScoreBar currentScore={null} />
      </ThemeProvider>,
      this.el
    );

    return this;
  }

  public remove() {
    return super.remove();
  }
}

export default ProductModelQualityScore;
