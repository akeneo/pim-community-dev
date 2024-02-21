import {
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_TAB_CHANGED,
} from '@akeneo-pim-community/data-quality-insights/src';

const BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');

class DataQualityInsightsTabContent extends BaseView {
  public configure() {
    this.trigger('tab:register', {
      code: this.code,
      label: __('akeneo_data_quality_insights.title'),
    });

    return super.configure();
  }

  public render() {
    this.el.insertAdjacentHTML(
      'beforeend',
      `
      <div id="${DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `
    );

    this.showTabContent();
    return this;
  }

  private showTabContent() {
    const productData = this.getFormData();
    const tab =
      productData.meta.model_type === 'product_model'
        ? PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME
        : PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME;
    window.dispatchEvent(
      new CustomEvent(PRODUCT_TAB_CHANGED, {
        detail: {
          currentTab: tab,
        },
      })
    );
  }
}

export = DataQualityInsightsTabContent;
