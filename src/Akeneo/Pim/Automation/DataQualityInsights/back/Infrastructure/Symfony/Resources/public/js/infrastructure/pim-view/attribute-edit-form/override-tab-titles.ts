import View from 'pimui/js/view/base-interface';

const translate = require('oro/translator');
const Router = require('pim/router');

export interface OverrideTabTitlesInterface {
  override(attributeCode: string): Promise<any>;
}

export default class OverrideTabTitles implements OverrideTabTitlesInterface {
  private readonly root: any;

  public constructor(root: any) {
    this.root = root;
  }

  public override(attributeCode: string): Promise<any> {
    const tabsTitle = [this.overrideLabelsTabTitle(attributeCode), this.overrideLabelOptionsTabTitle(attributeCode)];

    return Promise.all(tabsTitle);
  }

  private async overrideLabelsTabTitle(attributeCode: string) {
    return this.overrideTabTitle(
      'pim-attribute-edit-form-labels-tab',
      translate('pim_common.label_translations'),
      Router.generate('akeneo_data_quality_insights_get_attribute_labels_error_count', {
        attributeCode: attributeCode,
      })
    );
  }

  private async overrideLabelOptionsTabTitle(attributeCode: string) {
    return this.overrideTabTitle(
      'pim-attribute-edit-form-choices',
      translate('pim_enrich.entity.attribute_option.short_uppercase_label'),
      Router.generate('akeneo_data_quality_insights_get_attribute_options_labels_error_count', {
        attributeCode: attributeCode,
      })
    );
  }

  private async overrideTabTitle(tabCode: string, title: string, url: string) {
    const tabs = this.root.getExtension('pim-attribute-edit-form-form-tabs');
    if (!tabs) {
      return;
    }

    const registredTabs = tabs.getTabs();
    const registredTab = registredTabs.find((activeTab: any) => activeTab.code === tabCode);

    const tab = tabs.getExtension(tabCode);
    if (!tab || !registredTab) {
      return;
    }

    return $.ajax({
      url: url,
      type: 'GET',
    }).then(errorCount => {
      this.renderTabTitle(tab, title, errorCount);
    });
  }

  private renderTabTitle(tabModule: View, title: string, errorCount: number) {
    const errorCountLabel = `<span class="AknBadge AknBadge--important AknDataQualityInsightsQualityBadge--attribute-label-tab">${errorCount}</span>`;

    tabModule.trigger('tab:register', {
      code: tabModule.code,
      label: errorCount > 0 ? `${title} ${errorCountLabel}` : title,
    });
  }
}
