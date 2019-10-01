import Product, {
  NormalizedProduct,
  denormalizeProduct,
  PRODUCT_TYPE,
} from 'akeneoassetmanager/domain/model/product/product';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/product/completeness';
import {accessProperty} from 'akeneoassetmanager/tools/property';

const getProductCompleteness = (normalizedProduct: any): NormalizedCompleteness => {
  const completenessRatio = accessProperty(normalizedProduct, `completeness`, undefined);

  return {
    completeChildren: 0,
    totalChildren: 0,
    ratio: completenessRatio,
  };
};

const getProductModelCompleteness = (normalizedProduct: any): NormalizedCompleteness => {
  const completeChildren = accessProperty(normalizedProduct, `variant_product_completenesses.completeChildren`, 0);
  const totalChildren = accessProperty(normalizedProduct, 'variant_product_completenesses.totalChildren', 0);

  return {
    completeChildren,
    totalChildren,
    ratio: 0,
  };
};

export const hydrator = (denormalize: (denormalizeProduct: NormalizedProduct) => Product) => (
  normalizedProduct: any,
  context: {
    locale: LocaleReference;
  }
): Product => {
  const expectedKeys = ['id', 'identifier', 'document_type', 'label', 'image'];
  validateKeys(normalizedProduct, expectedKeys, 'The provided raw product seems to be malformed.');

  const completeness =
    PRODUCT_TYPE === normalizedProduct.document_type
      ? getProductCompleteness(normalizedProduct)
      : getProductModelCompleteness(normalizedProduct);

  return denormalize({
    id: String(normalizedProduct.id),
    identifier: normalizedProduct.identifier,
    type: normalizedProduct.document_type,
    labels: {[localeReferenceStringValue(context.locale)]: normalizedProduct.label},
    image: normalizedProduct.image,
    completeness,
  });
};

const hydrateProduct = hydrator(denormalizeProduct);

export default hydrateProduct;
