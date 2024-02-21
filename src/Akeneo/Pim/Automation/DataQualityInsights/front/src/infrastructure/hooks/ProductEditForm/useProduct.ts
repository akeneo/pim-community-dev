import {useSelector} from 'react-redux';
import {ProductEditFormState} from '../../store';

const useProduct = () => {
  return useSelector((state: ProductEditFormState) => state.product);
};

export default useProduct;
