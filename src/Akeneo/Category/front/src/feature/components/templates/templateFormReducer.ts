export type TemplateFormState = {
  attributes: {
    [uuid: string]: {
      [localeCode: string]: {
        value: string;
        errors: string[];
      };
    };
  };
  properties: {};
};

export type TemplateFormAction =
  | {
      type: 'attribute_label_translation_changed';
      payload: {
        attributeUuid: string;
        localeCode: string;
        value: string;
      };
    }
  | {
      type: 'attribute_label_translation_saved';
      payload: {
        attributeUuid: string;
        localeCode: string;
        value: string;
      };
    }
  | {
      type: 'save_attribute_label_translation_failed';
      payload: {
        attributeUuid: string;
        localeCode: string;
        errors: string[];
      };
    };

export const templateFormReducer = (previousState: TemplateFormState, action: TemplateFormAction) => {
  const state = (window as any).structuredClone(previousState);

  if (action.type === 'attribute_label_translation_changed') {
    const {attributeUuid, localeCode, value} = action.payload;
    state.attributes[attributeUuid] = {
      ...state.attributes[attributeUuid],
      [localeCode]: {
        value,
        errors: [],
      },
    };
  }

  if (action.type === 'attribute_label_translation_saved') {
    const {attributeUuid, localeCode, value} = action.payload;
    if (state.attributes[attributeUuid][localeCode].value === value) {
      delete state.attributes[attributeUuid][localeCode];
    }
  }

  if (action.type === 'save_attribute_label_translation_failed') {
    const {attributeUuid, localeCode, errors} = action.payload;
    state.attributes[attributeUuid][localeCode].errors = errors;
  }

  console.debug(action, previousState, state);

  return state;
};
