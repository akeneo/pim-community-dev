import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {CatalogContextListener, PageContextListener, ProductContextListener} from './listener';
import {Product} from '../domain';
import fetchProductModel from '../infrastructure/fetcher/ProductEditForm/fetchProductModel';
import {DataQualityInsightsTabContent} from './component/ProductEditForm/TabContent';
import fetchProductModelEvaluation from '../infrastructure/fetcher/ProductEditForm/fetchProductModelEvaluation';
import {AxesContextProvider} from './context/AxesContext';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

interface ProductModelEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductModelEditFormApp: FunctionComponent<ProductModelEditFormAppProps> = ({
  product,
  catalogChannel,
  catalogLocale,
}) => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Provider store={productEditFormStore}>
        <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
        <PageContextListener />
        <ProductContextListener product={product} productFetcher={fetchProductModel} />

        <AttributesTabContent product={product} />

        <AxesContextProvider axes={['enrichment']}>
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation} />
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductModelEditFormApp;
