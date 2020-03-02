import React, {FunctionComponent} from 'react';
import {Provider} from "react-redux";
import {productEditFormStore} from "../infrastructure/store";
import {CatalogContextListener, PageContextListener, ProductContextListener} from "./listener";
import {Product} from "../domain";
import {AttributesTabContent, DataQualityInsightsTabContent,} from "./component/ProductEditForm";
import fetchProductModelEvaluation from "../infrastructure/fetcher/fetchProductModelEvaluation";

interface ProductModelEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductModelEditFormApp: FunctionComponent<ProductModelEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <Provider store={productEditFormStore}>
      <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
      <PageContextListener />
      <ProductContextListener product={product}/>

      <AttributesTabContent product={product}/>
      <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation} />
    </Provider>
  );
};

export default ProductModelEditFormApp;
