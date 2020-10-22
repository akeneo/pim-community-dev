import React, {FunctionComponent} from 'react';
import {Provider} from "react-redux";
import {productEditFormStore} from "../infrastructure/store";
import {Product} from "../domain";
import {AxesContextProvider} from "@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext";
import {
  CatalogContextListener,
  PageContextListener,
  ProductContextListener
} from "@akeneo-pim-community/data-quality-insights/src/application/listener";
import AttributesTabContent from "./component/ProductEditForm/TabContent/AttributesTabContent";
import {DataQualityInsightsTabContent} from "@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent";
import AxisEvaluation
  from "@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation";
import Criterion
  from "@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion";
import {Recommendation} from "@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Recommendation";
import {fetchProductModelEvaluation} from "@akeneo-pim-community/data-quality-insights/src";
import fetchProductModel
  from "@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/ProductEditForm/fetchProductModel";
import {ThemeProvider} from "styled-components";
import {pimTheme} from "akeneo-design-system";
import {
  checkFollowingAttributeOptionSpellingCriterionActive,
  checkFollowingAttributeSpellingCriterionActive,
  followAttributeOptionSpellingCriterion,
  followAttributeSpellingCriterion
} from "./user-actions";

const translate = require('oro/translator');

interface ProductModelEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductModelEditFormApp: FunctionComponent<ProductModelEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Provider store={productEditFormStore}>
        <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
        <PageContextListener />
        <ProductContextListener product={product} productFetcher={fetchProductModel}/>

        <AttributesTabContent product={product}/>

        <AxesContextProvider axes={['enrichment', 'consistency']}>
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation}>
            <AxisEvaluation axis={'enrichment'}>
              <Criterion code={'completeness_of_non_required_attributes'}/>
              <Criterion code={'completeness_of_required_attributes'}/>
              <Criterion code={'missing_image_attribute'}>
                <Recommendation supports={(criterion => criterion.improvable_attributes.length === 0)}>
                  <span className="NotApplicableAttribute">{translate('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')}</span>
                </Recommendation>
              </Criterion>
            </AxisEvaluation>

            <AxisEvaluation axis={"consistency"}>
              <Criterion code={'consistency_spelling'}/>
              <Criterion code={'consistency_textarea_lowercase_words'}/>
              <Criterion code={'consistency_textarea_uppercase_words'}/>
              <Criterion code={'consistency_text_title_formatting'}/>
              <Criterion code={'consistency_attribute_spelling'} follow={followAttributeSpellingCriterion} isFollowingActive={checkFollowingAttributeSpellingCriterionActive}/>
              <Criterion code={'consistency_attribute_option_spelling'} follow={followAttributeOptionSpellingCriterion} isFollowingActive={checkFollowingAttributeOptionSpellingCriterionActive}/>
            </AxisEvaluation>
          </DataQualityInsightsTabContent>
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductModelEditFormApp;
