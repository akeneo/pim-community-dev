import {Completeness, Product, PRODUCT_TYPE} from 'akeneoassetmanager/domain/model/product/product';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {accessProperty} from 'akeneoassetmanager/tools/property';

const getProductCompleteness = (normalizedProduct: any): Completeness => {
  const completenessRatio = accessProperty(normalizedProduct, `completeness`, undefined);

  return {
    completeChildren: 0,
    totalChildren: 0,
    ratio: completenessRatio,
  };
};

const getProductModelCompleteness = (normalizedProduct: any): Completeness => {
  const completeChildren = accessProperty(normalizedProduct, `variant_product_completenesses.completeChildren`, 0);
  const totalChildren = accessProperty(normalizedProduct, 'variant_product_completenesses.totalChildren', 0);

  return {
    completeChildren,
    totalChildren,
    ratio: 0,
  };
};

export const hydrator = (
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

  return {
    id: String(normalizedProduct.id),
    identifier: normalizedProduct.identifier,
    type: normalizedProduct.document_type,
    labels: {[localeReferenceStringValue(context.locale)]: normalizedProduct.label},
    image: normalizedProduct.image,
    completeness,
  };
};

export default hydrator;
