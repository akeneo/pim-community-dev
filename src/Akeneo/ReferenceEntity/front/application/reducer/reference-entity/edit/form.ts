import {NormalizedReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import formState, {FormState} from 'akeneoreferenceentity/application/reducer/state';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {combineReducers} from 'redux';
import {NormalizedFile} from 'akeneoreferenceentity/domain/model/file';

export interface EditionFormState {
  state: FormState;
  data: NormalizedReferenceEntity;
  errors: ValidationError[];
}

const stateReducer = formState(
  'referenceEntity',
  'ENRICHED_ENTITY_EDITION_UPDATED',
  'ENRICHED_ENTITY_EDITION_RECEIVED'
);

const dataReducer = (
  state: NormalizedReferenceEntity = {identifier: '', code: '', labels: {}, image: null},
  {
    type,
    referenceEntity,
    value,
    locale,
    image,
  }: {type: string; referenceEntity: NormalizedReferenceEntity; value: string; locale: string; image: NormalizedFile}
) => {
  switch (type) {
    case 'ENRICHED_ENTITY_EDITION_RECEIVED':
      state = referenceEntity;
      break;
    case 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [locale]: value}};
      break;
    case 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED':
      state = {...state, image};
      break;
    default:
      break;
  }

  return state;
};

const errorsReducer = (state: ValidationError[] = [], action: {type: string; errors: ValidationError[]}) => {
  switch (action.type) {
    case 'ENRICHED_ENTITY_EDITION_SUBMISSION':
      state = [];
      break;
    case 'ENRICHED_ENTITY_EDITION_ERROR_OCCURED':
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
