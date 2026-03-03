enum ProductType {
  Product = 'product',
  ProductModel = 'product_model',
}

enum ProductsType {
  Products = 'products',
  ProductModels = 'product_models',
}

const getProductsType = (productType: ProductType): ProductsType =>
  ProductType.Product === productType ? ProductsType.Products : ProductsType.ProductModels;

const getProductType = (productsType: ProductsType): ProductType =>
  ProductsType.Products === productsType ? ProductType.Product : ProductType.ProductModel;

type VariantProductCompleteness = {
  completeChildren: number;
  totalChildren: number;
};

type Image = {
  filePath: string;
  originalFileName: string;
};

type Product = {
  id: number | string;
  identifier: string;
  label: string;
  document_type: ProductType;
  image: Image | null;
  completeness: number | null;
  variant_product_completenesses: VariantProductCompleteness | null;
};

export {Product, ProductType, ProductsType, getProductsType, getProductType};
