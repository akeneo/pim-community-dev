import React, {FunctionComponent} from 'react';
import {Provider} from 'react-redux';
import {productEditFormStore} from '../infrastructure/store';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {
  CatalogContextListener,
  PageContextListener,
  ProductContextListener,
} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {fetchProduct} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher';
import AttributesTabContent from './component/ProductEditForm/TabContent/AttributesTabContent';
import {DataQualityInsightsTabContent} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent';
import {AxisRatesOverviewPortal} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm';
import {fetchProductDataQualityEvaluation} from '@akeneo-pim-community/data-quality-insights/src';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

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

        <AxesContextProvider axes={['enrichment', 'consistency']}>
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
