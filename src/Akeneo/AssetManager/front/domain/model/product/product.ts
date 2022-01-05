import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import {File} from 'akeneoassetmanager/domain/model/file';

const PRODUCT_TYPE = 'product';
const PRODUCT_MODEL_TYPE = 'product_model';
type ProductType = 'product' | 'product_model';

const isProductModel = ({type}: Product): boolean => PRODUCT_MODEL_TYPE === type;

type Completeness = {completeChildren: number; totalChildren: number; ratio: number | null};

type ProductIdentifier = string;
type ProductId = string;
type Product = {
  id: ProductId;
  identifier: ProductIdentifier;
  type: ProductType;
  labels: LabelCollection;
  image: File;
  completeness: Completeness;
};

export {PRODUCT_TYPE, PRODUCT_MODEL_TYPE, isProductModel, Product, Completeness, ProductType};
