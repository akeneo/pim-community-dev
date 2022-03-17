import React from 'react';
import ReactDOM from 'react-dom';

import {AttributeCreateFormApp, ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from '@akeneo-pim-ee/data-quality-insights';

import BaseView from 'pimui/js/view/base';

class DataQualityInsightsApp extends BaseView {
  private renderingCount = 0;

  public configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', (tab: string) => {
      window.dispatchEvent(
        new CustomEvent(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, {
          detail: {
            currentTab: tab,
          },
        })
      );
    });

    return super.configure();
  }

  public render() {
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
