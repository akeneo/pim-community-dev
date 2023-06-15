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
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
      },
    },
    properties: {},
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_changed',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut 1',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
        fr_FR: {
          value: 'Attribut 1',
          errors: [],
        },
      },
    },
    properties: {},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should remove attribute label translation when attribute_label_translation_saved action is dispatched with matching value', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
        fr_FR: {
          value: 'Attribut 1',
          errors: [],
        },
      },
    },
    properties: {},
  };
  const action: TemplateFormAction = {
    type: 'attribute_label_translation_saved',
    payload: {
      attributeUuid: 'attribute-1',
      localeCode: 'fr_FR',
      value: 'Attribut 1',
    },
  };
  const expectedState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
      },
    },
    properties: {},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});

it('should add errors to attribute label translation when save_attribute_label_translation_failed action is dispatched', () => {
  const previousState: TemplateFormState = {
    attributes: {
      'attribute-1': {
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
        fr_FR: {
          value: 'Attribut 1',
          errors: [],
        },
      },
    },
    properties: {},
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
        en_US: {
          value: 'Attribute 1',
          errors: [],
        },
        fr_FR: {
          value: 'Attribut 1',
          errors: ['Error 1', 'Error 2'],
        },
      },
    },
    properties: {},
  };

  const newState = templateFormReducer(previousState, action);

  expect(newState).toEqual(expectedState);
});
