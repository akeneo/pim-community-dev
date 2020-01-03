import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from "react-redux";
import {Product} from "../../domain";
import {getProductFamilyInformationAction, initializeProductAction} from "../reducer";
import {fetchFamilyInformation} from "../fetcher";

interface ProductContextProviderProps {
  product: Product;
}

export const DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE = 'data-quality:product:show_attribute';
export const DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES = 'data-quality:product:filter_all_missing_attributes';
export const DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES = 'data-quality:product:filter_all_improvable_attributes';

const ProductContextProvider: FunctionComponent<ProductContextProviderProps> = ({product, children}) => {
  const dispatchAction = useDispatch();

  useEffect(() => {
    dispatchAction(initializeProductAction(product));

    if (!product.family) {
      return;
    }

    (async () => {
      const data = await fetchFamilyInformation(product.family as string);
      dispatchAction(getProductFamilyInformationAction(data));
    })();

  }, [product, product.family, dispatchAction]);

  return (
    <>{children}</>
  )
};

export default ProductContextProvider;
