import ProductInterface from 'pimfront/product-grid/domain/model/product';

export const dataReceived = (products: ProductInterface[], total: number, append: boolean) => {
  return {type: 'DATA_RECEIVED', data: {items: products}, total, append};
};

export const childrenReceived = (identifier: string, children: ProductInterface[]) => {
  return {type: 'CHILDREN_RECEIVED', children, identifier};
};
