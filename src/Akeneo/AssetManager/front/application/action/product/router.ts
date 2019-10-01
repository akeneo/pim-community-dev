import {redirectToRoute} from 'akeneoassetmanager/application/event/router';
import Product from 'akeneoassetmanager/domain/model/product/product';
import {productIdentifierStringValue} from 'akeneoassetmanager/domain/model/product/identifier';

export const redirectToProduct = (product: Product) => {
  return redirectToRoute(`pim_enrich_${product.getType()}_edit`, {
    id: productIdentifierStringValue(product.getId()),
  });
};

export const redirectToAttributeCreation = () => {
  return redirectToRoute(`pim_enrich_attribute_create`, {attribute_type: 'akeneo_asset'});
};
