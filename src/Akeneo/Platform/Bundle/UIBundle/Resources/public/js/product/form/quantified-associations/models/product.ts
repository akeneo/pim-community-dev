type ProductType = 'product' | 'product_model';
type ProductsType = 'products' | 'product_models';

const getProductsType = (productType: ProductType): ProductsType =>
  'product' === productType ? 'products' : 'product_models';

const getProductType = (productsType: ProductsType): ProductType =>
  'products' === productsType ? 'product' : 'product_model';

type VariantProductCompleteness = {
  completeChildren: number;
  totalChildren: number;
};

type Image = {
  filePath: string;
  originalFileName: string;
};

type Product = {
  id: number;
  identifier: string;
  label: string;
  document_type: ProductType;
  image: Image | null;
  completeness: number | null;
  variant_product_completenesses: VariantProductCompleteness | null;
};

export {Product, ProductType, ProductsType, getProductsType, getProductType};
