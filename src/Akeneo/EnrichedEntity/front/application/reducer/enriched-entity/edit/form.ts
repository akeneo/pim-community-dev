import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import formState, {FormState} from 'akeneoenrichedentity/application/reducer/state';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {combineReducers} from 'redux';

export interface EditionFormState {
  state: FormState;
  data: NormalizedEnrichedEntity;
  errors: ValidationError[];
}

const stateReducer = formState('enrichedEntity', 'ENRICHED_ENTITY_EDITION_UPDATED', 'ENRICHED_ENTITY_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedEnrichedEntity = {identifier: '', labels: {}},
  action: {type: string; enrichedEntity: NormalizedEnrichedEntity; value: string; locale: string}
) => {
  switch (action.type) {
    case 'ENRICHED_ENTITY_EDITION_RECEIVED':
      state = action.enrichedEntity;
      break;
    case 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [action.locale]: action.value}};
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
