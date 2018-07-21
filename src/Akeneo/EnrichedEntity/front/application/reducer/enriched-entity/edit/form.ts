import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import formState, {FormState} from 'akeneoenrichedentity/application/reducer/state';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {combineReducers} from 'redux';
import Image from 'akeneoenrichedentity/domain/model/image';

export interface EditionFormState {
  state: FormState;
  data: NormalizedEnrichedEntity;
  errors: ValidationError[];
}

const stateReducer = formState('enrichedEntity', 'ENRICHED_ENTITY_EDITION_UPDATED', 'ENRICHED_ENTITY_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedEnrichedEntity = {identifier: '', labels: {}, image: null},
  {
    type,
    enrichedEntity,
    value,
    locale,
    image,
  }: {type: string; enrichedEntity: NormalizedEnrichedEntity; value: string; locale: string; image: Image | null}
) => {
  switch (type) {
    case 'ENRICHED_ENTITY_EDITION_RECEIVED':
      state = enrichedEntity;
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
