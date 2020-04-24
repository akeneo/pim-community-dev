import reducer from 'akeneoassetmanager/application/reducer/asset-family/edit/form';

const initialState = {
  data: {
    attributeAsLabel: '',
    attributeAsMainMedia: '',
    attributes: [],
    identifier: '',
    code: '',
    labels: {},
    image: null,
    transformations: [],
    productLinkRules: '[]',
    namingConvention: '{}',
  },
  errors: [],
  state: {
    isDirty: false,
    originalData: '',
  },
};

describe('akeneo > asset family > application > reducer > asset-family --- edit', () => {
  test('I ignore other commands', () => {
    const newState = reducer(initialState, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(initialState);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual(initialState);
  });

  test('I can receive an asset family', () => {
    const state = {};
    const normalizedAssetFamily = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
      image: null,
    };
    const newState = reducer(state, {
      type: 'ASSET_FAMILY_EDITION_RECEIVED',
      assetFamily: normalizedAssetFamily,
    });

    expect(newState).toEqual({
      errors: [],
      data: normalizedAssetFamily,
      state: {isDirty: false, originalData: '{"identifier":"designer","labels":{"en_US":"Designer"},"image":null}'},
    });
  });

  test('I can update the label of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_LABEL_UPDATED',
      value: 'Famous Designer',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Famous Designer',
        },
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can add a new label of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_LABEL_UPDATED',
      value: 'Concepteur',
      locale: 'fr_FR',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
          fr_FR: 'Concepteur',
        },
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the transformations of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
        transformations: '[]',
      },
      errors: [],
      state: {isDirty: false, originalData: ''},
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_TRANSFORMATIONS_UPDATED',
      transformations: '["new transformations"]',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {},
        image: null,
        transformations: '["new transformations"]',
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the naming convention of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
        namingConvention: '{}',
      },
      errors: [],
      state: {isDirty: false, originalData: ''},
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_NAMING_CONVENTION_UPDATED',
      namingConvention: '{"new": "namingConvention"]',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {},
        image: null,
        namingConvention: '{"new": "namingConvention"]',
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the product link rules of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
        productLinkRules: '[]',
      },
      errors: [],
      state: {isDirty: false, originalData: ''},
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_PRODUCT_LINK_RULES_UPDATED',
      productLinkRules: '{"new": "rule"}',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {},
        image: null,
        productLinkRules: '{"new": "rule"}',
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the image of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_IMAGE_UPDATED',
      image: {my: 'image'},
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: {my: 'image'},
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the attribute as main media of the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        attributeAsMainMedia: 'old',
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_ATTRIBUTE_AS_MAIN_MEDIA_UPDATED',
      attributeAsMainMedia: 'new',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        attributeAsMainMedia: 'new',
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can successfully save the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      errors: [
        {
          my: 'error',
        },
      ],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_SUBMISSION',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I cannot save the asset family', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'ASSET_FAMILY_EDITION_ERROR_OCCURED',
      errors: [
        {
          my: 'error',
        },
      ],
    });

    expect(newState).toEqual({
      errors: [
        {
          my: 'error',
        },
      ],
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });
});
