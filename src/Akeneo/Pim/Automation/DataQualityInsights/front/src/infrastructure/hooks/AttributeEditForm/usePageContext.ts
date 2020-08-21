import {useSelector} from 'react-redux';

import AttributeEditFormState, {AttributeEditFormPageContextState} from "../../../application/state/AttributeEditFormState";
import PageContextHook from "@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/PageContextHook";

const usePageContext: PageContextHook<AttributeEditFormPageContextState> = () => {
  return useSelector((state: AttributeEditFormState) => state.pageContext);
};

export default usePageContext;
