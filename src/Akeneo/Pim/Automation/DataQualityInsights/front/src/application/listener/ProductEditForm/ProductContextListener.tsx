import React, {FunctionComponent, useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {Product} from '../../../domain';
import {getProductFamilyInformationAction, initializeProductAction} from '../../../infrastructure/reducer';
import {fetchFamilyInformation} from '../../../infrastructure/fetcher';
import ProductFetcher from '../../../infrastructure/fetcher/ProductEditForm/ProductFetcher';

interface ProductContextListenerProps {
  product: Product;
  productFetcher: ProductFetcher;
}

export const DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE = 'data-quality:product:show_attribute';
export const DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES = 'data-quality:product:filter_all_missing_attributes';
export const DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES =
  'data-quality:product:filter_all_improvable_attributes';
export const DATA_QUALITY_INSIGHTS_PRODUCT_SAVING = 'data-quality:product:saving';
export const DATA_QUALITY_INSIGHTS_PRODUCT_SAVED = 'data-quality:product:saved';

const ProductContextListener: FunctionComponent<ProductContextListenerProps> = ({product, productFetcher}) => {
  const [productHasToBeReloaded, setProductHasToBeReloaded] = useState(false);
  const dispatchAction = useDispatch();

  useEffect(() => {
    const handleProductSaving = () => {
      // do nothing
    };
    const handleProductSaved = () => {
      setProductHasToBeReloaded(true);
    };

    window.addEventListener(DATA_QUALITY_INSIGHTS_PRODUCT_SAVING, handleProductSaving);
    window.addEventListener(DATA_QUALITY_INSIGHTS_PRODUCT_SAVED, handleProductSaved);

    return () => {
      window.removeEventListener(DATA_QUALITY_INSIGHTS_PRODUCT_SAVING, handleProductSaving);
      window.removeEventListener(DATA_QUALITY_INSIGHTS_PRODUCT_SAVED, handleProductSaved);
    };
  }, []);

  useEffect(() => {
    dispatchAction(initializeProductAction(product));
  }, [product]);

  useEffect(() => {
    if (!product.family) {
      return;
    }

    (async () => {
      const data = await fetchFamilyInformation(product.family as string);
      dispatchAction(getProductFamilyInformationAction(data));
    })();
  }, [product.family]);

  useEffect(() => {
    if (productHasToBeReloaded) {
      (async () => {
        const data = await productFetcher(product.meta.id as number);
        dispatchAction(initializeProductAction(data));
        setProductHasToBeReloaded(false);
      })();
    }
  }, [productHasToBeReloaded]);

  return <></>;
};

export default ProductContextListener;
