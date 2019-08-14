import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import formState, {FormState} from 'akeneoreferenceentity/application/reducer/state';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {combineReducers} from 'redux';
import {NormalizedFile} from 'akeneoreferenceentity/domain/model/file';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';

export interface EditionFormState {
  state: FormState;
  data: NormalizedRecord;
  errors: ValidationError[];
}

const stateReducer = formState('record', 'RECORD_EDITION_UPDATED', 'RECORD_EDITION_RECEIVED');

const dataReducer = (
  state: NormalizedRecord = {
    identifier: '',
    reference_entity_identifier: '',
    code: '',
    labels: {},
    image: null,
    values: [],
  },
  {
    type,
    record,
    label,
    locale,
    image,
    value,
  }: {
    type: string;
    record: NormalizedRecord;
    label: string;
    locale: string;
    image: NormalizedFile;
    value: NormalizedValue;
  }
) => {
  switch (type) {
    case 'RECORD_EDITION_RECEIVED':
      state = record;
      break;
    case 'RECORD_EDITION_LABEL_UPDATED':
      state = {...state, labels: {...state.labels, [locale]: label}};
      break;
    case 'RECORD_EDITION_IMAGE_UPDATED':
      state = {...state, image};
      break;
    case 'RECORD_EDITION_VALUE_UPDATED':
      state = {
        ...state,
        values: state.values.map((currentValue: NormalizedValue) => {
          if (
            currentValue.channel === value.channel &&
            currentValue.locale === value.locale &&
            currentValue.attribute.identifier === value.attribute.identifier &&
            currentValue.data !== value.data
          ) {
            return value;
          }

          return currentValue;
        }),
      };
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
    case 'RECORD_EDITION_ERROR_OCCURRED':
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
