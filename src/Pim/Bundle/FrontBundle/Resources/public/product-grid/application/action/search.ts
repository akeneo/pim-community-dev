const fetcherRegistry = require('pim/fetcher-registry');
import Product, { ProductInterface, RawProductInterface } from 'pimfront/product/domain/model/product';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';
import { dataReceived } from 'pimfront/product-grid/domain/action/search';
import { State } from 'pimfront/grid/application/reducer/reducer';

export const productHidrator = (product: any): RawProductInterface => {
  return Product.clone(product);
};

const stateToQuery = (state: State<Product>) => {
  return {
    locale: state.user.catalogLocale,
    channel: state.user.catalogChannel,
    limit: state.grid.query.limit,
    page: state.grid.query.page
  }
}

export const updateResultsAction = () => (dispatch: any, getState: any): void => {
  return fetcherRegistry.getFetcher('product-grid')
    .search(stateToQuery(getState()))
    .then((products: RawProductInterface[]) => {
      dispatch(dataReceived(hidrateAll<ProductInterface>(productHidrator)(products)));
    });
};
