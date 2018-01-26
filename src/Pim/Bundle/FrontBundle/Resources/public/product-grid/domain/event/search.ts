import ProductInterface from 'pimfront/product/domain/model/product';

export const dataReceived = (products: ProductInterface[], append: boolean) => {
  return {type: 'DATA_RECEIVED', data: {items: products}, append};
};

export const childrenReceived = (identifier: string, products: ProductInterface[]) => {
  return {type: 'CHILDREN_RECEIVED', products, identifier};
};
