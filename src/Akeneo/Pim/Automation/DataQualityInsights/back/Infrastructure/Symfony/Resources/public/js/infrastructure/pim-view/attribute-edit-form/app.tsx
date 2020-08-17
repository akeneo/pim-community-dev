import React from 'react';
import ReactDOM from 'react-dom';

import {AttributeEditFormApp} from 'akeneodataqualityinsights-react';
import {ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from 'akeneodataqualityinsights-react/application/constant';
import OverrideTabTitles, {OverrideTabTitlesInterface} from "./override-tab-titles";

import BaseView from 'pimui/js/view/base';
import {ATTRIBUTE_EDIT_FORM_UPDATED} from "@akeneo-pim-ee/data-quality-insights/src/application/constant";

class DataQualityInsightsApp extends BaseView {
  private renderingCount = 0;
  private overrideTabTitles: OverrideTabTitlesInterface;

  public configure() {
    this.overrideTabTitles = new OverrideTabTitles(this.getRoot());

    this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', (tab: string) => {
      window.dispatchEvent(new CustomEvent(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, {detail: {
        currentTab: tab,
      }}));
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', () => {
      this.renderTabTitles();
    });

    window.addEventListener(ATTRIBUTE_EDIT_FORM_UPDATED, () => {
      this.renderTabTitles();
    });

    return super.configure();
  }

  public render() {
    this.renderingCount += 1;

    const attribute = this.getFormData();
    ReactDOM.render(<AttributeEditFormApp attribute={attribute} renderingId={this.renderingCount}/>, this.el);

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);
    this.renderingCount = 0;

    return super.remove();
  }

  private renderTabTitles() {
    const attributeCode = this.getFormData().code;

    this.overrideTabTitles.override(attributeCode).then(() => {
      this.render();
    });
  }
}

export = DataQualityInsightsApp;
