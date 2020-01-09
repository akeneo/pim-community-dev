import React, {FunctionComponent} from 'react';
import {Provider} from "react-redux";
import {productEditFormStore} from "../infrastructure/store";
import {CatalogContextProvider, PageContextProvider, ProductContextProvider} from "../infrastructure/context-provider";
import {Product} from "../domain";
import {
  AttributesTabContentPortal,
  AxisRatesOverviewPortal,
  DataQualityInsightsTabContentPortal
} from "./component/ProductEditForm";

interface ProductEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductEditFormApp: FunctionComponent<ProductEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <Provider store={productEditFormStore}>
      <CatalogContextProvider catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
      <PageContextProvider />
      <ProductContextProvider product={product}/>

      <AttributesTabContentPortal/>
      <DataQualityInsightsTabContentPortal />
      <AxisRatesOverviewPortal />
    </Provider>
  );
};

export default ProductEditFormApp;
