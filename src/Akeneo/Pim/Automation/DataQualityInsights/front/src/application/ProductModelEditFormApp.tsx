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
import {fetchProductModelEvaluation} from "@akeneo-pim-community/data-quality-insights/src";
import fetchProductModel
  from "@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/ProductEditForm/fetchProductModel";
import {ThemeProvider} from "styled-components";
import {pimTheme} from "akeneo-design-system";

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
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation} />
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductModelEditFormApp;
