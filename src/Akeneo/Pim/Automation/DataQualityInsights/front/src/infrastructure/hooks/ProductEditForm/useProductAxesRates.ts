import {useEffect} from 'react';
import {useSelector, useDispatch} from 'react-redux';
import {ProductEditFormState} from '../../store';
import {getProductAxesRatesAction} from '../../reducer';

const useProductAxesRates = () => {
  const dispatchAction = useDispatch();

  const {productId, productUpdated, axesRates} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const productUpdated = state.product.updated;
    const axesRates = productId ? state.productAxesRates[productId] : undefined;

    return {
      productId: productId,
      productUpdated,
      axesRates,
    };
  });

  useEffect(() => {
    if (productId && axesRates === undefined) {
      dispatchAction(getProductAxesRatesAction(productId, {}));
    }
  }, [productId, axesRates]);

  return {
    axesRates,
    productId,
    productUpdated,
  };
};

export default useProductAxesRates;
