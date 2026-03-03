import React, {FunctionComponent, useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {Product} from '../../../domain';
import {
  endProductEvaluationAction,
  getProductFamilyInformationAction,
  initializeProductAction,
  startProductEvaluationAction,
} from '../../../infrastructure/reducer';
import {fetchFamilyInformation} from '../../../infrastructure/fetcher';
import ProductFetcher from '../../../infrastructure/fetcher/ProductEditForm/ProductFetcher';
import {useEvaluateProduct} from '../../../infrastructure/hooks';

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
export const DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB = 'data-quality:redirect:dqi-tab';

const ProductContextListener: FunctionComponent<ProductContextListenerProps> = ({product, productFetcher}) => {
  const [productHasBeenSaved, setProductHasBeenSaved] = useState(false);
  const dispatchAction = useDispatch();
  const evaluateProduct = useEvaluateProduct(product);

  useEffect(() => {
    const handleProductSaving = () => {
      // do nothing
    };
    const handleProductSaved = () => {
      setProductHasBeenSaved(true);
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
    if (productHasBeenSaved) {
      (async () => {
        dispatchAction(startProductEvaluationAction());
        try {
          await evaluateProduct();
        } catch (e) {
        } finally {
          dispatchAction(endProductEvaluationAction());
        }
      })();

      (async () => {
        const data = await productFetcher(product.meta.id as string);
        dispatchAction(initializeProductAction(data));
        setProductHasBeenSaved(false);
      })();
    }
  }, [productHasBeenSaved, evaluateProduct]);

  return <></>;
};

export default ProductContextListener;
