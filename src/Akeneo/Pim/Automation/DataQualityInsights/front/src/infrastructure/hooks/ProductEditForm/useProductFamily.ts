import {useSelector} from 'react-redux';

import {ProductEditFormState} from '../../store';

const useProductFamily = () => {
  return useSelector((state: ProductEditFormState) => {
    const product = state.product;
    const familyCode = product.family;
    return familyCode ? state.families[familyCode] : null;
  });
};

export default useProductFamily;
