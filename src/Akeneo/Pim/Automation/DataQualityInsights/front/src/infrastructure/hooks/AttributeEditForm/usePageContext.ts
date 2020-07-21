import {useSelector} from 'react-redux';

import PageContextHook from "../PageContextHook";
import {AttributeEditFormPageContextState} from "../../../application/state/PageContextState";
import AttributeEditFormState from "../../../application/state/AttributeEditFormState";

const usePageContext: PageContextHook<AttributeEditFormPageContextState> = () => {
  return useSelector((state: AttributeEditFormState) => state.pageContext);
};

export default usePageContext;
