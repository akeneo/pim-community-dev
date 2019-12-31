import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';

import {fetchProductDataQualityEvaluation} from '../fetcher';
import {ProductEditFormState} from "../store";
import {getProductEvaluationAction} from "../reducer";

const useFetchProductDataQualityEvaluation = () => {
  const {productId, evaluation} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const evaluation = productId ? state.productEvaluation[productId] : {};

    return {
      evaluation: evaluation || {},
      productId: productId,
    };
  });

  const dispatchAction = useDispatch();

  useEffect(() => {
    (async () => {
      if (!productId) {
        return;
      }
      const data = await fetchProductDataQualityEvaluation(productId);
      dispatchAction(getProductEvaluationAction(productId, data));
    })();
  }, [productId, dispatchAction]);

  return evaluation;
};

export default useFetchProductDataQualityEvaluation;
