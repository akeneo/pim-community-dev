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
import AxisEvaluation from './component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation';
import Criterion from './component/ProductEditForm/TabContent/DataQualityInsights/Criterion';

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
          <DataQualityInsightsTabContent product={product} productEvaluationFetcher={fetchProductModelEvaluation}>
            <AxisEvaluation axis={'enrichment'} showRate={false}>
              <Criterion code={'completeness_of_required_attributes'} />
              <Criterion code={'completeness_of_non_required_attributes'} />
            </AxisEvaluation>
          </DataQualityInsightsTabContent>
        </AxesContextProvider>
      </Provider>
    </ThemeProvider>
  );
};

export default ProductModelEditFormApp;
