import React from 'react';
import ReactDOM from 'react-dom';

import {AttributeCreateFormApp,} from 'akeneodataqualityinsights-react';
import {
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
} from '@akeneo-pim-community/data-quality-insights/src';
import {ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from 'akeneodataqualityinsights-react/application/constant';

import BaseView from 'pimui/js/view/base';

class DataQualityInsightsApp extends BaseView {
  private isDataQualityInsightsEnabled = false;
  private renderingCount = 0;

  public initialize(): void {
    super.initialize();

    getDataQualityInsightsFeature().then((dataQualityInsightsFeature: DataQualityInsightsFeature) => {
      this.isDataQualityInsightsEnabled = dataQualityInsightsFeature.isActive ;
    });
  }

  public configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', (tab: string) => {
      window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, {detail: {
        currentTab: tab,
      }}));
    });

    return super.configure();
  }

  public render() {
    if (!this.isDataQualityInsightsEnabled) {
      return this;
    }

    this.renderingCount += 1;

    const attribute = this.getFormData();

    ReactDOM.render(<AttributeCreateFormApp attribute={attribute} renderingId={this.renderingCount} />, this.el);

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);
    this.renderingCount = 0;

    return super.remove();
  }
}

export = DataQualityInsightsApp;
