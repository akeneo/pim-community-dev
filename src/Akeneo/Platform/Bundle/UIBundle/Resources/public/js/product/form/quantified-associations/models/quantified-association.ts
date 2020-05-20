import {Identifier, Row, ProductType, getProductsType, ProductsType} from '../models';

type QuantifiedLink = {
  identifier: Identifier;
  quantity: number;
};

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: QuantifiedAssociation;
};

const isQuantifiedAssociationCollectionEmpty = (value: QuantifiedAssociationCollection) =>
  !Object.values(value).some(
    quantifiedAssociation =>
      quantifiedAssociation.products.length !== 0 || quantifiedAssociation.product_models.length !== 0
  );

const quantifiedAssociationCollectionToRowCollection = (collection: QuantifiedAssociationCollection): Row[] =>
  Object.keys(collection).reduce((result: Row[], associationTypeCode) => {
    const products = collection[associationTypeCode].products || [];
    const productModels = collection[associationTypeCode].product_models || [];

    return [
      ...result,
      ...products.map(({identifier, quantity}) => ({
        associationTypeCode,
        identifier,
        quantity,
        productType: ProductType.Product,
        product: null,
      })),
      ...productModels.map(({identifier, quantity}) => ({
        associationTypeCode,
        identifier,
        quantity,
        productType: ProductType.ProductModel,
        product: null,
      })),
    ];
  }, []);

const rowCollectionToQuantifiedAssociationCollection = (rows: Row[]): QuantifiedAssociationCollection =>
  rows.reduce(
    (
      quantifiedAssociationCollection: QuantifiedAssociationCollection,
      {productType, associationTypeCode, identifier, quantity}: Row
    ): QuantifiedAssociationCollection => {
      if (!(associationTypeCode in quantifiedAssociationCollection)) {
        quantifiedAssociationCollection[associationTypeCode] = {
          [ProductsType.Products]: [],
          [ProductsType.ProductModels]: [],
        };
      }

      quantifiedAssociationCollection[associationTypeCode][getProductsType(productType)].push({
        identifier,
        quantity,
      });

      return quantifiedAssociationCollection;
    },
    {}
  );

export {
  QuantifiedAssociationCollection,
  isQuantifiedAssociationCollectionEmpty,
  quantifiedAssociationCollectionToRowCollection,
  rowCollectionToQuantifiedAssociationCollection,
};
