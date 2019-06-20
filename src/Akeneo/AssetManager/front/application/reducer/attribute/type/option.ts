import {NormalizedOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import sanitize from 'akeneoreferenceentity/tools/sanitize';

const optionAttributeReducer = (
  normalizedAttribute: NormalizedOptionAttribute | NormalizedOptionCollectionAttribute
): NormalizedOptionAttribute | NormalizedOptionCollectionAttribute => {
  // Nothing to edit
  return normalizedAttribute;
};

export const reducer = optionAttributeReducer;

export interface EditOptionState {
  isActive: boolean;
  isDirty: boolean;
  isSaving: boolean;
  options: NormalizedOption[];
  currentOptionId: number;
  errors: ValidationError[];
  originalData: string;
  numberOfLockedOptions: any;
}

const initEditOptionState = (): EditOptionState => ({
  isDirty: false,
  isActive: false,
  isSaving: false,
  options: [],
  currentOptionId: 0,
  errors: [],
  originalData: '',
  numberOfLockedOptions: 0,
});

type Action = {
  type: string;
  options: NormalizedOption[];
  label: string;
  code: string;
  id: number;
  locale: string;
  errors: ValidationError[];
};

const isDirty = (state: EditOptionState, newData: NormalizedOption[]) => {
  return state.originalData !== JSON.stringify(newData);
};

const labelReducer = (
  option: NormalizedOption,
  {label, locale}: {label: string; locale: string},
  shouldGenerateCode: boolean
) => {
  const previousLabel = option.labels[locale];
  const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
  const code = expectedSanitizedCode === option.code && shouldGenerateCode ? sanitize(label) : option.code;

  if ('' === label) {
    const cleanedLabels = {...option.labels};
    delete cleanedLabels[locale];

    return {...option, code, labels: cleanedLabels};
  }

  return {...option, code, labels: {...option.labels, [locale]: label}};
};

const filterEmptyOptions = (options: NormalizedOption[]) => {
  return options.filter(
    (option: NormalizedOption) =>
      '' !== option.code || Object.keys(option.labels).some((locale: string) => '' !== option.labels[locale])
  );
};

export const editOptionsReducer = (state: EditOptionState = initEditOptionState(), action: Action) => {
  const {type, options, id, code, errors} = action;

  switch (type) {
    case 'OPTIONS_EDITION_START':
      return {
        ...state,
        isActive: true,
        isSaving: false,
        isDirty: false,
        originalData: JSON.stringify(options),
        options,
        numberOfLockedOptions: options.length,
        errors: [],
      };

    case 'OPTIONS_EDITION_CANCEL':
    case 'DISMISS':
      return {
        ...state,
        isActive: false,
        isDirty: false,
      };

    case 'OPTIONS_EDITION_SELECTED':
      return {
        ...state,
        currentOptionId: id,
      };

    case 'OPTIONS_EDITION_LABEL_UPDATED':
      const shouldGenerateCode = id >= state.numberOfLockedOptions;
      const newLabelOptions = [...state.options];
      newLabelOptions[id] =
        undefined !== newLabelOptions[id]
          ? labelReducer(newLabelOptions[id], action, shouldGenerateCode)
          : labelReducer(Option.createEmpty().normalize(), action, true);

      return {
        ...state,
        options: filterEmptyOptions(newLabelOptions),
        isDirty: isDirty(state, newLabelOptions),
      };

    case 'OPTIONS_EDITION_CODE_UPDATED':
      const newCodeOptions = [...state.options];
      newCodeOptions[id] =
        undefined !== newCodeOptions[id] ? {...newCodeOptions[id], code} : {...Option.createEmpty().normalize(), code};

      return {
        ...state,
        options: filterEmptyOptions(newCodeOptions),
        isDirty: isDirty(state, newCodeOptions),
      };

    case 'OPTIONS_EDITION_SUBMISSION':
      return {...state, isSaving: true, errors: []};

    case 'OPTIONS_EDITION_SUCCEEDED':
      return {
        ...state,
        isActive: false,
        isDirty: false,
        numberOfLockedOptions: state.options.length,
      };

    case 'OPTIONS_EDITION_ERROR_OCCURED':
      return {...state, isSaving: false, errors};

    case 'OPTIONS_EDITION_DELETE':
      const filteredOptions = state.options.filter((_option: NormalizedOption, index: number) => index !== id);
      return {
        ...state,
        options: filteredOptions,
        isDirty: isDirty(state, filteredOptions),
        currentOptionId: 1 <= id ? id - 1 : 0,
        numberOfLockedOptions:
          id <= state.numberOfLockedOptions - 1 ? state.numberOfLockedOptions - 1 : state.numberOfLockedOptions,
      };

    default:
      break;
  }

  return state;
};
