import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {CatalogContextListener, PageContextListener, ProductContextListener} from './listener';
import {Product} from '../domain';
import {fetchProduct, fetchProductDataQualityEvaluation} from '../infrastructure/fetcher';
import {AxisRatesOverviewPortal} from './component/ProductEditForm';
import {AxesContextProvider} from './context/AxesContext';
import {DataQualityInsightsTabContent} from './component/ProductEditForm/TabContent';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

interface ProductEditFormAppProps {
  catalogChannel: string;
  catalogLocale: string;
  product: Product;
}

const ProductEditFormApp: FunctionComponent<ProductEditFormAppProps> = ({product, catalogChannel, catalogLocale}) => {
  return (
    <ThemeProvider theme={pimTheme}>
      <Provider store={productEditFormStore}>
        <CatalogContextListener catalogChannel={catalogChannel} catalogLocale={catalogLocale} />
        <PageContextListener />
        <ProductContextListener product={product} productFetcher={fetchProduct} />

        <AttributesTabContent product={product} />

        <AxesContextProvider axes={['enrichment']}>
          <DataQualityInsightsTabContent
            product={product}
            productEvaluationFetcher={fetchProductDataQualityEvaluation}
          />
          <AxisRatesOverviewPortal />
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductEditFormApp;
