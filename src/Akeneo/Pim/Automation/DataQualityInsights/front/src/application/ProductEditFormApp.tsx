import React, {FunctionComponent} from 'react';
import {Provider} from "react-redux";
import {productEditFormStore} from "../infrastructure/store";
import {CatalogContextListener, PageContextListener, ProductContextListener} from "./listener";
import {Product} from "../domain";
import {
  AttributesTabContent,
  AxisRatesOverviewPortal,
  DataQualityInsightsTabContent,
} from "./component/ProductEditForm";

interface ProductEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductEditFormApp: FunctionComponent<ProductEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <Provider store={productEditFormStore}>
      <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
      <PageContextListener />
      <ProductContextListener product={product}/>

      <AttributesTabContent/>
      <DataQualityInsightsTabContent />
      <AxisRatesOverviewPortal />
    </Provider>
  );
};

export default ProductEditFormApp;
