import Product from 'pimfront/product/domain/model/product';
import { redirectToRoute } from 'pimfront/app/application/event/router';

export const redirectToProduct = (product: Product) => {
  return redirectToRoute('pim_enrich_product_edit', {id: product.meta.id});
}
