import {ATTRIBUTE_EDIT_FORM_UPDATED} from '@akeneo-pim-ee/data-quality-insights/src/application/constant';

const BaseView = require('pimui/js/view/base');
const __ = require('oro/translator');
const Router = require('pim/router');

class QualitySummaryHeader extends BaseView {
  private attributeEditFormUpdatedHandler = () => {
    this.render();
  };

  public configure() {
    window.addEventListener(ATTRIBUTE_EDIT_FORM_UPDATED, this.attributeEditFormUpdatedHandler);

    return super.configure();
  }

  public render() {
    const url = Router.generate(
      'akeneo_data_quality_insights_get_number_of_products_impacted_by_attribute_spelling_mistakes',
      {
        attributeCode: this.getFormData().code,
      }
    );

    $.ajax({
      url: url,
      type: 'GET',
    }).then(impactedProductsNumber => {
      if (impactedProductsNumber === 0) {
        this.$el.html('');

        return;
      }

      this.$el.html(`
        <div class="AknDescriptionHeader-AttributeEditForm">
          <div class="AknDescriptionHeader-AttributeEditForm-Icon">
            <img src="/bundles/pimui/images/icon-info.svg"/>
          </div>
          <div>
            ${__('akeneo_data_quality_insights.attribute_edit_form.quality_summary', {count: impactedProductsNumber})}
          </div>
        </div>
      `);
    });

    return this;
  }

  public remove() {
    super.remove();
    window.removeEventListener(ATTRIBUTE_EDIT_FORM_UPDATED, this.attributeEditFormUpdatedHandler);

    return this;
  }
}

export = QualitySummaryHeader;
