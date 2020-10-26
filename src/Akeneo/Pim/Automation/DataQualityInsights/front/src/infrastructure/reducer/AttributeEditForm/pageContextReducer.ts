import {Action, Reducer} from 'redux';
import {AttributeEditFormPageContextState} from '../../../application/state/AttributeEditFormState';

export type UpdatePageContextAction = UpdateTabContextAction;

interface UpdateTabContextAction extends Action {
  payload: {
    currentTab: string | null;
  };
}

export const CHANGE_TAB = 'CHANGE_TAB';
export const changeAttributeEditFormTabAction = (tabName: string | null): UpdateTabContextAction => {
  return {
    type: CHANGE_TAB,
    payload: {
      currentTab: tabName,
    },
  };
};

const initialState: AttributeEditFormPageContextState = {
  currentTab: null,
};

const pageContextReducer: Reducer<AttributeEditFormPageContextState, UpdatePageContextAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case CHANGE_TAB:
      return {
        ...previousState,
        currentTab: payload.currentTab,
      };
    default:
      return previousState;
  }
};
export default pageContextReducer;
