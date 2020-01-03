import React from 'react';
import ReactDOM from 'react-dom';
import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
  ProductEditFormApp
} from 'akeneodataqualityinsights-react';

const UserContext = require('pim/user-context');
const BaseView = require('pimui/js/view/base');
const FieldManager = require('pim/field-manager');

interface LocaleEvent {
  localeCode: string;
  context: string;
}

interface ScopeEvent {
  scopeCode: string;
  context: string;
}

interface ShowAttributeEvent {
  code: boolean;
}

interface FilterAttributesEvent{
  attributes: string[];
}

class DataQualityInsightsApp extends BaseView {
  private isDataQualityInsightsEnabled = false;

  public initialize(): void {
    super.initialize();

    getDataQualityInsightsFeature().then((dataQualityInsightsFeature: DataQualityInsightsFeature) => {
      this.isDataQualityInsightsEnabled = dataQualityInsightsFeature.isActive ;
    });
  }

  public configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', (event: LocaleEvent) => {
      window.dispatchEvent(new CustomEvent(CATALOG_CONTEXT_LOCALE_CHANGED, {detail: {
        locale: event.localeCode,
        context: event.context
      }}));
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', (event: ScopeEvent) => {
      window.dispatchEvent(new CustomEvent(CATALOG_CONTEXT_CHANNEL_CHANGED, {detail: {
        channel: event.scopeCode,
        context: event.context
      }}));
    });

    window.addEventListener(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, ((event: CustomEvent<ShowAttributeEvent>) => {
      this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all');
      this.redirectToProductEditForm();
      this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:after', (_: ScopeEvent) => {
        FieldManager.getField(event.detail.code).then(function (field: any) {
          field.setFocus();
        });
      });
    }) as EventListener);

    window.addEventListener(DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES, ((_: CustomEvent<FilterAttributesEvent>) => {
      this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all_missing_attributes');
      this.redirectToProductEditForm();
    }));

    window.addEventListener(DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES, ((_: CustomEvent<FilterAttributesEvent>) => {
      this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'all_improvable_attributes');
      this.redirectToProductEditForm();
    }));

    return super.configure();
  }

  public redirectToProductEditForm() {
    this.getRoot().trigger('column-tab:change-tab', {
      currentTarget: {
        dataset: {
          tab: 'pim-product-edit-form-attributes'
        }
      },
      target: {
        dataset: {
          tab: 'pim-product-edit-form-attributes'
        }
      }
    });
  }

  public render() {
    if (!this.isDataQualityInsightsEnabled) {
      return this;
    }

    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');
    const productData = this.getFormData();

    ReactDOM.render(
      <ProductEditFormApp catalogLocale={catalogLocale} catalogChannel={catalogChannel} product={productData} />,
      this.el
    );

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = DataQualityInsightsApp;
