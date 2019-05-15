import Product, {
  NormalizedProduct,
  denormalizeProduct,
  PRODUCT_TYPE,
} from 'akeneoreferenceentity/domain/model/product/product';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';

export const hydrator = (denormalize: (denormalizeProduct: NormalizedProduct) => Product) => (
  normalizedProduct: any
): Product => {
  const expectedKeys = ['meta'];
  validateKeys(normalizedProduct, expectedKeys, 'The provided raw attribute seems to be malformed.');

  return denormalize({
    id: String(normalizedProduct.meta.id),
    identifier:
      PRODUCT_TYPE === normalizedProduct.meta.model_type ? normalizedProduct.identifier : normalizedProduct.code,
    type: normalizedProduct.meta.model_type,
    labels: normalizedProduct.meta.label,
    image: normalizedProduct.meta.image,
  });
};

const hydrateAttribute = hydrator(denormalizeProduct);

export default hydrateAttribute;
