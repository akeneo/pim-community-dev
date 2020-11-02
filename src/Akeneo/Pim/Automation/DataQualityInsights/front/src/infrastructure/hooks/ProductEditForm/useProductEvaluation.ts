import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {ProductEditFormState} from '../../store';
import {getProductEvaluationAction} from '../../reducer';

const useProductEvaluation = () => {
  const dispatchAction = useDispatch();

  const {evaluation, productId, productUpdated} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const productUpdated = state.product.updated;
    const evaluation = productId ? state.productEvaluation[productId] : undefined;

    return {
      evaluation: evaluation,
      productId: productId,
      productUpdated,
    };
  });

  useEffect(() => {
    if (productId && evaluation === undefined) {
      dispatchAction(getProductEvaluationAction(productId, {}));
    }
  }, [productId, evaluation]);

  return {
    evaluation,
    productId,
    productUpdated,
  };
};

export default useProductEvaluation;
