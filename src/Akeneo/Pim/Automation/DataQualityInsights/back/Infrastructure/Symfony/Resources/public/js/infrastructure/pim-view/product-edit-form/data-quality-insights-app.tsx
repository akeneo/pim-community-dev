import React from 'react';
import ReactDOM from 'react-dom';
import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
  ProductEditFormApp
} from 'akeneodataqualityinsights-react';

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

    return super.configure();
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
