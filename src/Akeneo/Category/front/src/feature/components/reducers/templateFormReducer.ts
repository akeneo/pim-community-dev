export type TemplateFormState = {
  attributes: {
    [uuid: string]: {
      labels: {
        [localeCode: string]: {
          value: string;
          errors: string[];
        };
      };
    };
  };
  properties: {
    labels: {
      [localeCode: string]: {
        value: string;
        errors: string[];
      };
    };
  };
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
    }
  | {
      type: 'template_label_translation_changed';
      payload: {
        localeCode: string;
        value: string;
      };
    }
  | {
      type: 'template_label_translation_saved';
      payload: {
        localeCode: string;
        value: string;
      };
    }
  | {
      type: 'save_template_label_translation_failed';
      payload: {
        localeCode: string;
        errors: string[];
      };
    };

export const templateFormReducer = (previousState: TemplateFormState, action: TemplateFormAction) => {
  const state = (window as any).structuredClone(previousState) as TemplateFormState;

  if (action.type === 'attribute_label_translation_changed') {
    const {attributeUuid, localeCode, value} = action.payload;
    state.attributes[attributeUuid] = state.attributes[attributeUuid] || {labels: {}};
    state.attributes[attributeUuid].labels = {
      ...state.attributes[attributeUuid].labels,
      [localeCode]: {
        value,
        errors: [],
      },
    }
  }

  if (action.type === 'attribute_label_translation_saved') {
    const {attributeUuid, localeCode, value} = action.payload;
    if (state.attributes?.[attributeUuid]?.labels?.[localeCode]?.value === value) {
      delete state.attributes[attributeUuid].labels[localeCode];
    }
  }

  if (action.type === 'save_attribute_label_translation_failed') {
    const {attributeUuid, localeCode, errors} = action.payload;
    if (state.attributes?.[attributeUuid]?.labels?.[localeCode]) {
      state.attributes[attributeUuid].labels[localeCode].errors = errors;
    }
  }

  if (action.type === 'template_label_translation_changed') {
    const {localeCode, value} = action.payload;
    state.properties.labels[localeCode] = {
      value,
      errors: [],
    };
  }

  if (action.type === 'template_label_translation_saved') {
    const {localeCode, value} = action.payload;
    if (state.properties.labels[localeCode]?.value === value) {
      delete state.properties.labels[localeCode];
    }
  }

  if (action.type === 'save_template_label_translation_failed') {
    const {localeCode, errors} = action.payload;
    if (state.properties.labels[localeCode]) {
      state.properties.labels[localeCode].errors = errors;
    }
  }

  console.debug(action, previousState, state);

  return state;
};
