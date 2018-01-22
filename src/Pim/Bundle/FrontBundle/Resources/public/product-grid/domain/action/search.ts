import { ProductInterface } from 'pimfront/product/domain/model/product';

export const dataReceived = (products: ProductInterface[], append: boolean) => {
    return {type: 'DATA_RECEIVED', data: {items: products}, append};
};
