import {useSelector} from 'react-redux';

import {ProductEditFormState} from '../../store';
import PageContextHook from '../PageContextHook';
import {ProductEditFormPageContextState} from '../../../application/state/PageContextState';

const usePageContext: PageContextHook<ProductEditFormPageContextState> = () => {
  return useSelector((state: ProductEditFormState) => state.pageContext);
};

export default usePageContext;
