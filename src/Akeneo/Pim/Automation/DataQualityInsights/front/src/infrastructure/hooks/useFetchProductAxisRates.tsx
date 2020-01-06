import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';

import {fetchProductAxisRates} from '../fetcher';
import {ProductEditFormState} from "../store";
import {getProductAxisRatesAction} from "../reducer";

const useFetchProductAxisRates = () => {
    const {evaluation, productId} = useSelector((state: ProductEditFormState) => {
      const productId = state.product.meta.id;
      const evaluation = productId ? state.productAxisRates[productId] : {};

      return {
        evaluation: evaluation || {},
        productId: productId,
      };
    });

    const dispatchAction = useDispatch();

    useEffect(() => {
      if (!productId) {
        return;
      }

      (async () => {
          const data = await fetchProductAxisRates(productId);
          dispatchAction(getProductAxisRatesAction(productId, data));
      })();
    }, [productId, dispatchAction]);

    return evaluation;
};

export default useFetchProductAxisRates;
