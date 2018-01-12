import Product, { RawProductInterface } from 'pimfront/js/product/domain/model/product';
import hidrateAll from './hidrator';

const hidrator = (product: any): RawProductInterface => {
  return Product.clone(product);
};

export default (products: any) => {
  return hidrateAll(hidrator)(products);
};
