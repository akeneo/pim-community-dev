const fetcherRegistry = require('pim/fetcher-registry');
import Product, { ProductInterface, RawProductInterface } from 'pimfront/product/domain/model/product';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';
import { dataReceived } from 'pimfront/product-grid/domain/action/search';

export const productHidrator = (product: any): RawProductInterface => {
  return Product.clone(product);
};

export const updateResultsAction = (locale: string, channel: string) => (dispatch: any): void => {
  return fetcherRegistry.getFetcher('product-grid').search({
      limit: 25,
      'default_locale': locale,
      'default_scope': channel
    })
    .then((products: RawProductInterface[]) => {
      dispatch(dataReceived(hidrateAll<ProductInterface>(productHidrator)(products)));
    });
};
