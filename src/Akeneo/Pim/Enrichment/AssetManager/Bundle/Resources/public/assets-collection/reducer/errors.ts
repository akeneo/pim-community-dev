import {Action} from 'redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {ValidationError} from 'akeneopimenrichmentassetmanager/platform/model/validation-error';

export type ErrorsState = ValidationError[];

export const errorsReducer = (state: ErrorsState = [], action: ErrorsAddedAction | ErrorsRemovedAction) => {
  switch (action.type) {
    case 'ERRORS_RECEIVED':
      state = action.errors;
      break;
    case 'ERRORS_REMOVED_ALL':
      state = action.errors;
      break;
    default:
      break;
  }

  return state;
};

type ErrorsAddedAction = Action<'ERRORS_RECEIVED'> & {errors: ValidationError[]};
export const errorsReceived = (errors: ValidationError[]): ErrorsAddedAction => {
  return {type: 'ERRORS_RECEIVED', errors};
};

type ErrorsRemovedAction = Action<'ERRORS_REMOVED_ALL'> & {errors: ValidationError[]};
export const errorsRemovedAll = (): ErrorsRemovedAction => {
  return {type: 'ERRORS_REMOVED_ALL', errors: []};
};

export const selectErrors = (state: AssetCollectionState) => {
  return state.errors;
};
