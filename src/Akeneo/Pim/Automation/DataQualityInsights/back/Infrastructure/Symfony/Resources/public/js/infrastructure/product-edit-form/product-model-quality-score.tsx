import {QualityScoreProductModelHeader} from '@akeneo-pim-community/data-quality-insights';

const BaseView = require('pimui/js/view/base');

class ProductModelQualityScore extends BaseView {
  public render() {
    this.renderReact(QualityScoreProductModelHeader, {bar: true}, this.el);

    return this;
  }
}

export = ProductModelQualityScore;
