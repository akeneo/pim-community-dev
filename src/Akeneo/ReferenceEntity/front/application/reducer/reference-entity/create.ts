import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {
  NormalizedReferenceEntityCreation,
  createEmptyReferenceEntityCreation,
} from 'akeneoreferenceentity/domain/model/reference-entity/creation';
import sanitize from 'akeneoreferenceentity/tools/sanitize';

export interface CreateState {
  active: boolean;
  data: NormalizedReferenceEntityCreation;
  errors: ValidationError[];
}

const initCreationState = (): CreateState => ({
  active: false,
  data: createEmptyReferenceEntityCreation().normalize(),
  errors: [],
});

export default (
  state: CreateState = initCreationState(),
  action: {type: string; locale: string; value: string; errors: ValidationError[]}
) => {
  switch (action.type) {
    case 'REFERENCE_ENTITY_CREATION_START':
      state = {...initCreationState(), active: true};
      break;

    case 'REFERENCE_ENTITY_CREATION_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, code: action.value},
      };

      break;

    case 'REFERENCE_ENTITY_CREATION_LABEL_UPDATED':
      const previousLabel = state.data.labels[action.locale];
      const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
      const code = expectedSanitizedCode === state.data.code ? sanitize(action.value) : state.data.code;

      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, code},
      };

      break;

    case 'REFERENCE_ENTITY_CREATION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'REFERENCE_ENTITY_CREATION_SUBMISSION':
      state = {
        ...state,
        errors: [],
      };
      break;

    case 'REFERENCE_ENTITY_CREATION_SUCCEEDED':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'REFERENCE_ENTITY_CREATION_ERROR_OCCURED':
      state = {
        ...state,
        errors: action.errors,
      };
      break;
    default:
  }

  return state;
};
