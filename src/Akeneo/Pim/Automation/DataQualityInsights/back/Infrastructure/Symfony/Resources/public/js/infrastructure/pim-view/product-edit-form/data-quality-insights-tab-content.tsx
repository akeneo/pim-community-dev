import {
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_TAB_NAME,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
  PRODUCT_TAB_CHANGED
} from 'akeneodataqualityinsights-react';

const BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');

class DataQualityInsightsTabContent extends BaseView {

  private isDataQualityInsightsEnabled = false;

  public initialize(): void {
    super.initialize();

    getDataQualityInsightsFeature().then((dataQualityInsightsFeature: DataQualityInsightsFeature) => {
      this.isDataQualityInsightsEnabled = dataQualityInsightsFeature.isActive ;
    });
  }

  public configure() {
    this.trigger('tab:register', {
      code: this.code,
      label: __('akeneo_data_quality_insights.title'),
      isVisible: () => {
        return (this.isDataQualityInsightsEnabled === true);
      }
    });

    return super.configure();
  }

  public render() {
    this.el.insertAdjacentHTML('beforeend', `
      <div id="${DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `);

    this.showTabContent();
    return this;
  }

  private showTabContent() {
    window.dispatchEvent(new CustomEvent(PRODUCT_TAB_CHANGED, {detail: {
      currentTab: DATA_QUALITY_INSIGHTS_TAB_NAME,
    }}));
  }
}

export = DataQualityInsightsTabContent;
