import {redirectToRoute} from 'akeneoassetmanager/application/event/router';
import Product from 'akeneoassetmanager/domain/model/product/product';

export const redirectToProduct = (product: Product) => {
  return redirectToRoute(`pim_enrich_${product.getType()}_edit`, {
    id: product.getId().stringValue(),
  });
};

export const redirectToAttributeCreation = () => {
  return redirectToRoute(`pim_enrich_attribute_create`, {attribute_type: 'akeneo_asset'});
};
