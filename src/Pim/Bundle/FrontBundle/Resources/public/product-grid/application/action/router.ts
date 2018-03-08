import ProductInterface from 'pimfront/product-grid/domain/model/product';
import {redirectToRoute} from 'pimfront/app/application/event/router';

export const redirectToProduct = (product: ProductInterface) => {
  return redirectToRoute('pim_enrich_product_edit', {id: product.meta.id});
};
