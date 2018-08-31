import {NormalizedRecord} from 'akeneoenrichedentity/domain/model/record/record';
import formState, {FormState} from 'akeneoenrichedentity/application/reducer/state';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {combineReducers} from 'redux';
import Image from 'akeneoenrichedentity/domain/model/image';

export interface EditionFormState {
  state: FormState;
  data: NormalizedRecord;
  errors: ValidationError[];
}

const stateReducer = formState('record', 'RECORD_EDITION_UPDATED', 'RECORD_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedRecord = {identifier: '', enrichedEntityIdentifier: '', code: '', labels: {}, image: null},
  {
    type,
    record,
    value,
    locale,
    image,
  }: {type: string; record: NormalizedRecord; value: string; locale: string; image: Image | null}
) => {
  switch (type) {
    case 'RECORD_EDITION_RECEIVED':
      state = record;
      break;
    case 'RECORD_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [locale]: value}};
      break;
    case 'RECORD_EDITION_IMAGE_UPDATED':
      state = {...state, image};
      break;
    default:
      break;
  }

  return state;
};

const errorsReducer = (state: ValidationError[] = [], action: {type: string; errors: ValidationError[]}) => {
  switch (action.type) {
    case 'RECORD_EDITION_SUBMISSION':
      state = [];
      break;
    case 'RECORD_EDITION_ERROR_OCCURED':
      state = action.errors;
      break;
    default:
      break;
  }

  return state;
};

export default combineReducers({
  state: stateReducer,
  data: dataReducer,
  errors: errorsReducer,
});
