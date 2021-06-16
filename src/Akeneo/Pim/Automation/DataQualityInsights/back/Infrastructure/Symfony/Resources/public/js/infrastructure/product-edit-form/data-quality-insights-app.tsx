import React from 'react';
import ReactDOM from 'react-dom';
import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVED,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVING,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  PRODUCT_ATTRIBUTES_TAB_LOADED,
  PRODUCT_ATTRIBUTES_TAB_LOADING,
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_LEVEL_CHANGED,
  PRODUCT_TAB_CHANGED,
  ProductEditFormApp,
  ProductModelEditFormApp,
  DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
} from '@akeneo-pim-community/data-quality-insights/src';
const UserContext = require('pim/user-context');
const BaseView = require('pimui/js/view/base');

interface LocaleEvent {
  localeCode: string;
  context: string;
}

interface ScopeEvent {
  scopeCode: string;
  context: string;
}

interface FilterAttributesEvent {
  attributes: string[];
}

interface TabEvent {
  target: {
    dataset: {
      tab: string;
    };
  };
}

interface LevelNavigationEvent {
  id: number;
  model_type: string;
}

class DataQualityInsightsApp extends BaseView {
  public configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', (event: LocaleEvent) => {
      window.dispatchEvent(
        new CustomEvent(CATALOG_CONTEXT_LOCALE_CHANGED, {
          detail: {
            locale: event.localeCode,
            context: event.context,
          },
        })
      );
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', (event: ScopeEvent) => {
      window.dispatchEvent(
        new CustomEvent(CATALOG_CONTEXT_CHANNEL_CHANGED, {
          detail: {
            channel: event.scopeCode,
            context: event.context,
          },
        })
      );
    });

    window.addEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, (() => {
      this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all');
      this.redirectToAttributesTab();
    }) as EventListener);

    window.addEventListener(
      DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
      (_: CustomEvent<FilterAttributesEvent>) => {
        this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all_missing_attributes');
        this.redirectToAttributesTab();
      }
    );

    window.addEventListener(
      DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
      (_: CustomEvent<FilterAttributesEvent>) => {
        this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all_improvable_attributes');
        this.redirectToAttributesTab();
      }
    );

    window.addEventListener(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB, () => {
      this.redirectToDQITab();
    });

    this.listenTo(this.getRoot(), 'column-tab:select-tab', ({target}: TabEvent) => {
      window.dispatchEvent(
        new CustomEvent(PRODUCT_TAB_CHANGED, {
          detail: {
            currentTab: target.dataset.tab,
          },
        })
      );
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:before', () => {
      window.dispatchEvent(new Event(PRODUCT_ATTRIBUTES_TAB_LOADING));
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:after', () => {
      window.dispatchEvent(new Event(PRODUCT_ATTRIBUTES_TAB_LOADED));
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      window.dispatchEvent(new Event(DATA_QUALITY_INSIGHTS_PRODUCT_SAVING));
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', () => {
      window.dispatchEvent(new Event(DATA_QUALITY_INSIGHTS_PRODUCT_SAVED));
    });

    this.listenTo(
      this.getRoot(),
      'pim:product:variant-navigation:navigate-to-level:before',
      (event: LevelNavigationEvent) => {
        window.dispatchEvent(
          new CustomEvent(PRODUCT_MODEL_LEVEL_CHANGED, {
            detail: {
              id: event.id,
              model_type: event.model_type,
            },
          })
        );
      }
    );

    window.dispatchEvent(new Event(DATA_QUALITY_INSIGHTS_PRODUCT_SAVED));

    return super.configure();
  }

  public redirectToAttributesTab() {
    const productData = this.getFormData();
    const tab =
      productData.meta.model_type === 'product_model' ? PRODUCT_MODEL_ATTRIBUTES_TAB_NAME : PRODUCT_ATTRIBUTES_TAB_NAME;

    this.redirectToTab(tab);
  }

  public redirectToDQITab() {
    const productData = this.getFormData();
    const tab =
      productData.meta.model_type === 'product_model'
        ? PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME
        : PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME;

    this.redirectToTab(tab);
  }

  public redirectToTab(tab: string) {
    this.getRoot().trigger('column-tab:change-tab', {
      currentTarget: {
        dataset: {
          tab: tab,
        },
      },
      target: {
        dataset: {
          tab: tab,
        },
      },
    });
  }

  public render() {
    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');
    const productData = this.getFormData();

    ReactDOM.render(
      productData.meta.model_type === 'product_model' ? (
        <ProductModelEditFormApp catalogLocale={catalogLocale} catalogChannel={catalogChannel} product={productData} />
      ) : (
        <ProductEditFormApp catalogLocale={catalogLocale} catalogChannel={catalogChannel} product={productData} />
      ),
      this.el
    );

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export default DataQualityInsightsApp;
