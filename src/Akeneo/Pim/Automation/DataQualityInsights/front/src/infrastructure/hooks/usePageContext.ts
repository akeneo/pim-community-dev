import {useSelector} from 'react-redux';
import {ProductEditFormState} from "../store";

const usePageContext = () => {
  return useSelector((state: ProductEditFormState) => state.pageContext);
};

export default usePageContext;
