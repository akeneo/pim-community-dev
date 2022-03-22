import {AttributeEditFormApp, ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from '@akeneo-pim-ee/data-quality-insights';
import OverrideTabTitles, {OverrideTabTitlesInterface} from './override-tab-titles';

import BaseView from 'pimui/js/view/base';
import {ATTRIBUTE_EDIT_FORM_SPELLCHECK_IGNORED} from '@akeneo-pim-ee/data-quality-insights/src/application/constant';
import {ATTRIBUTE_OPTION_UPDATED, ATTRIBUTE_OPTION_DELETED} from 'akeneopimstructure/js/attribute-option/model/Events';

class DataQualityInsightsApp extends BaseView {
  private renderingCount = 0;
  private overrideTabTitles: OverrideTabTitlesInterface;
  private renderingTabTitle: boolean;
  private updateTabTitlesHandler: any;

  public configure() {
    this.overrideTabTitles = new OverrideTabTitles(this.getRoot());
    this.renderingTabTitle = false;
    this.updateTabTitlesHandler = this.renderTabTitles.bind(this);

    this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', (tab: string) => {
      window.dispatchEvent(
        new CustomEvent(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, {
          detail: {
            currentTab: tab,
          },
        })
      );
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.updateTabTitlesHandler);
    window.addEventListener(ATTRIBUTE_EDIT_FORM_SPELLCHECK_IGNORED, this.updateTabTitlesHandler);
    window.addEventListener(ATTRIBUTE_OPTION_UPDATED, this.updateTabTitlesHandler);
    window.addEventListener(ATTRIBUTE_OPTION_DELETED, this.updateTabTitlesHandler);

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
    window.removeEventListener(ATTRIBUTE_EDIT_FORM_SPELLCHECK_IGNORED, this.updateTabTitlesHandler);
    window.removeEventListener(ATTRIBUTE_OPTION_UPDATED, this.updateTabTitlesHandler);
    window.removeEventListener(ATTRIBUTE_OPTION_DELETED, this.updateTabTitlesHandler);
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
