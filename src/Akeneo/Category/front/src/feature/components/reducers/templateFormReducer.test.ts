import {TemplateFormAction, TemplateFormState, templateFormReducer} from './templateFormReducer';

beforeEach(() => {
  if (globalThis.structuredClone === undefined) {
    globalThis.structuredClone = (obj: unknown) => JSON.parse(JSON.stringify(obj));
  }
});

it('should update attribute label translation when attribute_label_translation_changed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_changed',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
          fr_FR: {
            value: 'Attribut FR',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should remove attribute label translation when attribute_label_translation_saved action is dispatched with matching value', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
          fr_FR: {
            value: 'Attribut FR',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_saved',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should not remove attribute label translation when attribute_label_translation_saved action is dispatched with a different value', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          fr_FR: {
            value: 'Attribut FR (being edited)',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_saved',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          fr_FR: {
            value: 'Attribut FR (being edited)',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should add errors to attribute label translation when save_attribute_label_translation_failed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
          fr_FR: {
            value: 'Attribut FR',
            errors: [],
          },
        },
      },
    },
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'save_attribute_label_translation_failed',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      errors: ['Error 1', 'Error 2'],
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          en_US: {
            value: 'Attribute US',
            errors: [],
          },
          fr_FR: {
            value: 'Attribut FR',
            errors: ['Error 1', 'Error 2'],
          },
        },
      },
    },
    properties: {labels: {}},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should update template label translation when template_label_translation_changed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {},
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'template_label_translation_changed',
    payload: {
      localeCode: 'fr_FR',
      value: 'Template FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR',
          errors: [],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should remove template label translation when template_label_translation_saved action is dispatched with matching value', () => {
  const previousState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        en_US: {
          value: 'Template US',
          errors: [],
        },
        fr_FR: {
          value: 'Template FR',
          errors: [],
        },
      },
    },
  };
  const action: TemplateFormAction = {
    type: 'template_label_translation_saved',
    payload: {
      localeCode: 'fr_FR',
      value: 'Template FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        en_US: {
          value: 'Template US',
          errors: [],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should not remove template label translation when template_label_translation_saved action is dispatched with a different value', () => {
  const previousState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR (being edited)',
          errors: [],
        },
      },
    },
  };
  const action: TemplateFormAction = {
    type: 'template_label_translation_saved',
    payload: {
      localeCode: 'fr_FR',
      value: 'Template FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR (being edited)',
          errors: [],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should add errors to template label translation when save_template_label_translation_failed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR',
          errors: [],
        },
      },
    },
  };
  const action: TemplateFormAction = {
    type: 'save_template_label_translation_failed',
    payload: {
      localeCode: 'fr_FR',
      errors: ['Error 1', 'Error 2'],
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR',
          errors: ['Error 1', 'Error 2'],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should not override errors on template label translation when attribute_label_translation_changed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {},
    properties: {
      labels: {
        fr_FR: {
          value: '',
          errors: ['Error 1', 'Error 2'],
        },
      },
    },
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_changed',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          fr_FR: {
            value: 'Attribut FR',
            errors: [],
          },
        },
      },
    },
    properties: {
      labels: {
        fr_FR: {
          value: '',
          errors: ['Error 1', 'Error 2'],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should not override errors on attribute label translation when template_label_translation_changed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          fr_FR: {
            value: '',
            errors: ['Error 1', 'Error 2'],
          },
        },
      },
    },
    properties: {labels: {}},
  };
  const action: TemplateFormAction = {
    type: 'template_label_translation_changed',
    payload: {
      localeCode: 'fr_FR',
      value: 'Template FR',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        labels: {
          fr_FR: {
            value: '',
            errors: ['Error 1', 'Error 2'],
          },
        },
      },
    },
    properties: {
      labels: {
        fr_FR: {
          value: 'Template FR',
          errors: [],
        },
      },
    },
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});
