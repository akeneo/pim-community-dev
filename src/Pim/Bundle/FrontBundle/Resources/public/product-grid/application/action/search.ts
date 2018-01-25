const fetcherRegistry = require('pim/fetcher-registry');
import ProductInterface, {
  Product,
  ProductModel,
  RawProductInterface,
  ModelType
} from 'pimfront/product/domain/model/product';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';
import { dataReceived } from 'pimfront/product-grid/domain/event/search';
import { State } from 'pimfront/grid/application/reducer/reducer';
import { Filter } from 'pimfront/grid/domain/model/query';
import { startLoading, stopLoading, goNextPage, goFirstPage } from 'pimfront/grid/application/event/search';

export const productHidrator = (product: RawProductInterface): ProductInterface => {
  switch (product.meta.model_type) {
    case ModelType.Product:
      return Product.create(product);
    case ModelType.ProductModel:
      return ProductModel.create(product);
    default:
      throw new Error(`Cannot handle model type ${product.meta.model_type}`);
  }

};

const stateToQuery = (state: State<Product>) => {
  return {
    locale: state.user.catalogLocale,
    channel: state.user.catalogChannel,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: state.grid.query.filters
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
  if (!getState().grid.isFetching && getState().grid.items.length < 500) {
    dispatch(goNextPage());
    dispatch(updateResultsAction(true));
  }
};

export const loadChildrenAction = (identifier: string) => (dispatch: any, getState: any) => {
  const state = getState();
  state.query.filters = [
    ...state.query.filters.filter((filter: Filter) => 'parent' !== filter.field),
    {
      field: 'parent',
      operator: 'IN',
      value: [identifier]
    }
  ];

  const children = await fetchResults();
};
