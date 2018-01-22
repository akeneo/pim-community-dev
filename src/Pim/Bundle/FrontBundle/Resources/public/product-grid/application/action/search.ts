const fetcherRegistry = require('pim/fetcher-registry');
import Product, { ProductInterface, RawProductInterface } from 'pimfront/product/domain/model/product';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';
import { dataReceived } from 'pimfront/product-grid/domain/action/search';
import { State } from 'pimfront/grid/application/reducer/reducer';
import { startLoading, stopLoading, goNextPage, goFirstPage } from 'pimfront/grid/application/event/search';

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
};

const fetchResults = async (state: State<Product>): Promise<ProductInterface[]> => {
  const products: RawProductInterface[] = await fetcherRegistry.getFetcher('product-grid')
    .search(stateToQuery(state));

  return hidrateAll<ProductInterface>(productHidrator)(products);
};

export const updateResultsAction = (append: boolean = false) => async (dispatch: any, getState: any): Promise<void> => {
  dispatch(startLoading());

  if (false === append) {
    dispatch(goFirstPage());
  }

  const products = await fetchResults(getState());

  dispatch(dataReceived(products, append));
  dispatch(stopLoading());
};

export const needMoreResultsAction = () => (dispatch: any, getState: any) => {
  if (!getState().grid.isFetching) {
    dispatch(goNextPage());
    dispatch(updateResultsAction(true));
  }
};
