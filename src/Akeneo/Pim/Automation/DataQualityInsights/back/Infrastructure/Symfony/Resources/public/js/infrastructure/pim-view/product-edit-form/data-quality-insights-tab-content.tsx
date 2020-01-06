import {
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature
} from 'akeneodataqualityinsights-react';

const BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');

const TAB_NAME = 'pim-product-edit-form-data-quality-insights-tab-content';

interface TabEvent {
  target: {
    dataset: {
      tab: string;
    };
  };
}

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

    this.listenTo(this.getRoot(), 'column-tab:select-tab', ({target}: TabEvent) => {
      this.showTabContent((target.dataset.tab === TAB_NAME));
    });

    return super.configure();
  }

  public render() {
    this.el.insertAdjacentHTML('beforeend', `
      <div id="${DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `);

    this.showTabContent(true);
    return this;
  }

  public remove() {
    this.showTabContent(false);

    return super.remove();
  }

  private showTabContent(isShown: boolean) {
    window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED, {detail: {
      show: isShown,
    }}));
  }
}

export = DataQualityInsightsTabContent;
