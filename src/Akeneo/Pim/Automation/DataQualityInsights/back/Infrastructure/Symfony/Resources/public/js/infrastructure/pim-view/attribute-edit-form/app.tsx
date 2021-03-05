import {AttributeEditFormApp} from 'akeneodataqualityinsights-react';
import {ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from 'akeneodataqualityinsights-react/application/constant';
import OverrideTabTitles, {OverrideTabTitlesInterface} from './override-tab-titles';

import BaseView from 'pimui/js/view/base';
import {ATTRIBUTE_EDIT_FORM_UPDATED} from '@akeneo-pim-ee/data-quality-insights/src/application/constant';

class DataQualityInsightsApp extends BaseView {
  private renderingCount = 0;
  private overrideTabTitles: OverrideTabTitlesInterface;
  private attributeEditFormUpdatedHandler: () => void;
  private renderingTabTitle: boolean;

  public configure() {
    this.overrideTabTitles = new OverrideTabTitles(this.getRoot());
    this.renderingTabTitle = false;
    this.attributeEditFormUpdatedHandler = () => {
      this.renderTabTitles();
    };

    this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', (tab: string) => {
      window.dispatchEvent(
        new CustomEvent(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, {
          detail: {
            currentTab: tab,
          },
        })
      );
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', () => {
      this.renderTabTitles();
    });

    window.addEventListener(ATTRIBUTE_EDIT_FORM_UPDATED, this.attributeEditFormUpdatedHandler);

    return super.configure();
  }

  public render() {
    this.renderingCount += 1;

    this.renderReact(
      AttributeEditFormApp,
      {
        // @ts-ignore
        attribute: this.getFormData(),
        renderingId: this.renderingCount,
      },
      this.el
    );
    return this;
  }

  remove() {
    window.removeEventListener(ATTRIBUTE_EDIT_FORM_UPDATED, this.attributeEditFormUpdatedHandler);
    super.remove();
    this.renderingCount = 0;

    return this;
  }

  private renderTabTitles() {
    if (this.renderingTabTitle) {
      return;
    }

    this.renderingTabTitle = true;
    const attributeCode = this.getFormData().code;

    this.overrideTabTitles.override(attributeCode).then(() => {
      this.render();
      this.renderingTabTitle = false;
    });
  }
}

export = DataQualityInsightsApp;
