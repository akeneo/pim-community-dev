import {Product} from "../../../domain";

export const isSimpleProduct = ((product: Product) => product.meta.level === null);

export const isVariantProduct = ((product: Product): boolean => product.meta.level !== null && product.meta.model_type === "product");

export const isProductModel = ((product: Product): boolean => product.meta.level !== null && product.meta.model_type === "product_model");
