import {NormalizedOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {Option, createEmptyOption} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {ValidationError} from '@akeneo-pim-community/shared';
import sanitize from 'akeneoassetmanager/tools/sanitize';

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
  options: Option[];
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
  options: Option[];
  label: string;
  code: string;
  id: number;
  locale: string;
  errors: ValidationError[];
};

const isDirty = (state: EditOptionState, newData: Option[]) => {
  return state.originalData !== JSON.stringify(newData);
};

const labelReducer = (
  option: Option,
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

const filterEmptyOptions = (options: Option[]) => {
  return options.filter(
    (option: Option) =>
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
        currentOptionId: 0,
      };

    case 'OPTIONS_EDITION_CANCEL':
    case 'DISMISS':
      return {
        ...state,
        isActive: false,
        isDirty: false,
        currentOptionId: 0,
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
          : labelReducer(createEmptyOption(), action, true);

      return {
        ...state,
        options: filterEmptyOptions(newLabelOptions),
        isDirty: isDirty(state, newLabelOptions),
      };

    case 'OPTIONS_EDITION_CODE_UPDATED':
      const newCodeOptions = [...state.options];
      newCodeOptions[id] =
        undefined !== newCodeOptions[id] ? {...newCodeOptions[id], code} : {...createEmptyOption(), code};

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
      const filteredOptions = state.options.filter((_option: Option, index: number) => index !== id);
      const filteredErrors = state.errors.filter(error => error.propertyPath !== `options.${id}`);
      return {
        ...state,
        options: filteredOptions,
        errors: filteredErrors,
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
