import {DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/Sidebar';

const BaseView = require('pimui/js/view/base');

class DataQualityInsightsOverview extends BaseView {
  public render() {
    this.el.insertAdjacentHTML(
      'beforeend',
      `
      <div id="${DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID}"></div>
    `
    );
    return this;
  }

  public remove() {
    return super.remove();
  }
}

export = DataQualityInsightsOverview;
