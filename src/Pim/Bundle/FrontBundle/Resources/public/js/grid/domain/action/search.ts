const fetcherRegistry = require('pim/fetcher-registry');
import { ProductInterface } from 'pimfront/js/product/domain/model/product';
import productHidrator from 'pimfront/js/grid/application/hidrator/product';

const dataReceived = (products: ProductInterface[]) => {
    return {type: 'DATA_RECEIVED', data: {items: products}};
};

export const updateGridAction = (locale: string, channel: string) => {
  return (dispatch: any): void => {
      return fetcherRegistry.getFetcher('product-grid').search({
          limit: 25,
          'default_locale': locale,
          'default_scope': channel
        })
        .then((products: ProductInterface[]) => {
          dispatch(dataReceived(productHidrator(products)));
        });
    };
};
