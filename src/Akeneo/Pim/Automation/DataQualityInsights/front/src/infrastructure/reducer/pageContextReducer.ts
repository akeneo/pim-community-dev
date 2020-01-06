import {Reducer} from 'redux';

export interface PageContextState {
  dqiTabContentVisibility: boolean;
}

interface UpdatePageContextAction {
  type: string;
  payload: {
    dqiTabContentVisibility?: boolean;
  }
}
export const CHANGE_DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY = 'CHANGE_DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY';

export const changeDataQualityInsightsTabContentVisibility = (show: boolean): UpdatePageContextAction => {
  return {
    type: CHANGE_DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY,
    payload: {
      dqiTabContentVisibility: show
    }
  };
};

const initialState: PageContextState = {
  dqiTabContentVisibility: false,
};

const pageContextReducer: Reducer<PageContextState, UpdatePageContextAction> = (previousState = initialState, {type, payload}) => {
  switch(type) {
    case CHANGE_DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY:
      return {
        ...previousState,
        dqiTabContentVisibility: payload.dqiTabContentVisibility || false
      };
    default:
      return previousState;
  }
};
export default pageContextReducer;
