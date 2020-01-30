import {useSelector} from "react-redux";
import {ProductEditFormState} from "../store";


const useProductAxesRates = () => {
  const {axesRates, productId, productUpdated} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const productUpdated = state.product.updated;
    const axesRates = productId ? state.productAxesRates[productId] : {};

    return {
      axesRates: axesRates || {},
      productId: productId,
      productUpdated,
    };
  });

  return {
    axesRates,
    productId,
    productUpdated
  };
};

export default useProductAxesRates;
