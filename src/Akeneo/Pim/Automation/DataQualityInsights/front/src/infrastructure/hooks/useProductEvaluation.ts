import {useSelector} from "react-redux";
import {ProductEditFormState} from "../store";


const useProductEvaluation = () => {
  const {evaluation, productId, productUpdated} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const productUpdated = state.product.updated;
    const evaluation = productId ? state.productEvaluation[productId] : {};

    return {
      evaluation: evaluation || {},
      productId: productId,
      productUpdated,
    };
  });

  return {
    evaluation,
    productId,
    productUpdated
  };
};

export default useProductEvaluation;
