import {
  structureReducer,
  attributeListUpdated,
  channelListUpdated,
  familyUpdated,
  ruleRelationListUpdated,
  selectAttributeList,
  selectChannels,
  selectLocales,
  selectFamily,
  selectRuleRelations,
  selectAttributeGroupList,
  updateChannels,
  updateFamily,
  updateRuleRelations,
  updateAttributeGroups,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {channelFetcher, fetchChannels} from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {
  familyFetcher,
  fetchFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family';
import {fetchRuleRelations} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/rule-relation';
import {
  attributeGroupFetcher,
  fetchAssetAttributeGroups,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute-group';

jest.mock('pim/fetcher-registry', () => {});
jest.mock('pimee/rule-manager', () => {});
fetchChannels = jest.fn();
fetchFamily = jest.fn();
fetchRuleRelations = jest.fn();
channelFetcher = jest.fn();
familyFetcher = jest.fn();
attributeGroupFetcher = jest.fn();
fetchAssetAttributeGroups = jest.fn();

/**
 *  REDUCER TESTS
 */

test('It ignores other commands', () => {
  const state = {};
  const newState = structureReducer(state, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toMatchObject(state);
});

test('It should generate a default state', () => {
  const newState = structureReducer(undefined, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toMatchObject({attributes: [], channels: [], family: null, rulesNumberByAttribute: {}});
});

test('It should update the attribute list', () => {
  const state = {attributes: [], channels: [], family: null, rulesNumberByAttribute: {}};
  const attribute = {
    code: 'packshot',
    labels: {
      en_US: 'packshot',
    },
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot',
  };
  const newState = structureReducer(state, {
    type: 'ATTRIBUTE_LIST_UPDATED',
    attributes: [attribute],
  });

  expect(newState).toMatchObject({attributes: [attribute], channels: [], family: null, rulesNumberByAttribute: {}});
});

test('It should update the channel list', () => {
  const state = {attributes: [], channels: [], family: null, rulesNumberByAttribute: {}};
  const channel = {
    code: 'ecommerce',
    labels: {
      en_US: 'E-commerce',
    },
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: 'english',
        region: 'United States',
      },
    ],
  };

  const newState = structureReducer(state, {
    type: 'CHANNEL_LIST_UPDATED',
    channels: [channel],
  });

  expect(newState).toMatchObject({attributes: [], channels: [channel], family: null, rulesNumberByAttribute: {}});
});

test('It should update the family', () => {
  const state = {attributes: [], channels: [], family: null, rulesNumberByAttribute: {}};
  const family = {
    code: 'marketing',
    attributeRequirements: {
      ecommerce: ['packshot'],
    },
  };

  const newState = structureReducer(state, {
    type: 'FAMILY_UPDATED',
    family,
  });

  expect(newState).toMatchObject({attributes: [], channels: [], family, rulesNumberByAttribute: {}});
});

test('It should update the rule relation list', () => {
  const state = {attributes: [], channels: [], family: null, rulesNumberByAttribute: {}};

  const newState = structureReducer(state, {
    type: 'RULE_RELATION_LIST_UPDATED',
    rulesNumberByAttribute: {packshot: 2},
  });

  expect(newState).toMatchObject({attributes: [], channels: [], family: null, rulesNumberByAttribute: {packshot: 2}});
});

/**
 *  ACTION CREATORS TESTS
 */

test('It should have an action to update the attribute list', () => {
  const attributes = [
    {
      code: 'packshot',
      labels: {
        en_US: 'packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
  ];
  const expectedAction = {
    type: 'ATTRIBUTE_LIST_UPDATED',
    attributes,
  };

  expect(attributeListUpdated(attributes)).toMatchObject(expectedAction);
});

test('It shoud have an action to update the channel list', () => {
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        en_US: 'E-commerce',
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        },
      ],
    },
  ];
  const expectedAction = {
    type: 'CHANNEL_LIST_UPDATED',
    channels,
  };

  expect(channelListUpdated(channels)).toMatchObject(expectedAction);
});

test('It should have an action to update the family', () => {
  const family = {
    code: 'marketing',
    attributeRequirements: {
      ecommerce: ['packshot'],
    },
  };
  const expectedAction = {
    type: 'FAMILY_UPDATED',
    family,
  };

  expect(familyUpdated(family)).toMatchObject(expectedAction);
});

test('It should have an action to update the rule relation list', () => {
  const rulesByAttribute = {packshot: 2};
  const expectedAction = {
    type: 'RULE_RELATION_LIST_UPDATED',
    rulesNumberByAttribute: rulesByAttribute,
  };

  expect(ruleRelationListUpdated(rulesByAttribute)).toMatchObject(expectedAction);
});

/**
 *  SELECTORS TESTS
 */

test('It should be able to select the attribute list from the state', () => {
  const attribute = {
    code: 'packshot',
    labels: {
      en_US: 'packshot',
    },
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot',
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [attribute], channels: [], family: null, rulesNumberByAttribute: {}},
    values: [],
  };

  expect(selectAttributeList(state)).toEqual([attribute]);
});

test('It should be able to select the channels from the state', () => {
  const channel = {
    code: 'ecommerce',
    labels: {
      en_US: 'E-commerce',
    },
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: 'english',
        region: 'United States',
      },
    ],
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null, rulesNumberByAttribute: {}},
    values: [],
  };

  expect(selectChannels(state)).toEqual([channel]);
});

test('It should be able to select locales from the state', () => {
  const locale = {
    code: 'en_US',
    label: 'English (United States)',
    language: 'english',
    region: 'United States',
  };
  const channel = {
    code: 'ecommerce',
    labels: {
      en_US: 'E-commerce',
    },
    locales: [locale],
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null, rulesNumberByAttribute: {}},
    values: [],
  };

  expect(selectLocales(state)).toEqual([locale]);
});

test('It should be able to select distinct locales from the state', () => {
  const locale = {
    code: 'en_US',
    label: 'English (United States)',
    language: 'english',
    region: 'United States',
  };
  const channel = {
    code: 'ecommerce',
    labels: {
      en_US: 'E-commerce',
    },
    locales: [locale, locale],
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null, rulesNumberByAttribute: {}},
    values: [],
  };

  expect(selectLocales(state)).toEqual([locale]);
});

test('It should be able to select a family from the state', () => {
  const family = {
    code: 'scanner',
    attributeRequirements: {
      ecommerce: ['packshot'],
    },
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family, rulesNumberByAttribute: {}},
    values: [],
  };

  expect(selectFamily(state)).toEqual(family);
});

test('It should be able to select rulesNumberByAttribute from the state', () => {
  const rulesByAttribute = {packshot: 2};
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null, rulesNumberByAttribute: rulesByAttribute},
    values: [],
  };

  expect(selectRuleRelations(state)).toEqual(rulesByAttribute);
});

/**
 *  THUNK FUNCTIONS TESTS
 */

test('It should be able to dispatch an action to update the channels', async () => {
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        en_US: 'E-commerce',
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        },
      ],
    },
  ];
  const dispatch = jest.fn();
  fetchChannels.mockImplementation(channelFetcher => () => channels);

  await updateChannels()(dispatch);
  expect(fetchChannels).toBeCalled();
  expect(channelFetcher).toBeCalled();
  expect(dispatch).toBeCalledWith({type: 'CHANNEL_LIST_UPDATED', channels});
});

test('It should be able to dispatch an action to update the family', async () => {
  const familyCode = 'scanner';
  const family = {
    code: 'scanner',
    attributeRequirements: {
      ecommerce: ['packshot'],
    },
  };
  const dispatch = jest.fn();
  fetchFamily.mockImplementation(familyFetcher => familyCode => family);

  await updateFamily(familyCode)(dispatch);
  expect(fetchFamily).toBeCalled();
  expect(familyFetcher).toBeCalled();
  expect(dispatch).toBeCalledWith({type: 'FAMILY_UPDATED', family});
});

test('It should be able to dispatch an action to update the rule relation list', async () => {
  const rulesNumberByAttribute = {packshot: 2};
  const dispatch = jest.fn();
  fetchRuleRelations.mockImplementation(() => rulesNumberByAttribute);

  await updateRuleRelations()(dispatch);
  expect(fetchRuleRelations).toBeCalled();
  expect(dispatch).toBeCalledWith({type: 'RULE_RELATION_LIST_UPDATED', rulesNumberByAttribute});
});

test('It should update the attribute group list', () => {
  const state = {attributes: [], attributeGroups: {}, channels: [], family: null, rulesNumberByAttribute: {}};
  const attributeGroups = {
    marketing: {
      code: 'marketing',
      sort_order: 1,
      labels: {en_US: 'Marketing Label US', fr_FR: 'Marketing Label FR', de_DE: 'Marketing Label DE'},
    },
  };
  const newState = structureReducer(state, {
    type: 'ATTRIBUTE_GROUP_LIST_UPDATED',
    attributeGroups,
  });

  expect(newState).toMatchObject({
    attributes: [],
    attributeGroups,
    channels: [],
    family: null,
    rulesNumberByAttribute: {},
  });
});

test('It should be able to select attribute groups from the state', () => {
  const attributeGroups = {
    marketing: {
      code: 'marketing',
      sort_order: 1,
      labels: {en_US: 'Marketing Label US', fr_FR: 'Marketing Label FR', de_DE: 'Marketing Label DE'},
    },
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null, rulesNumberByAttribute: {}, attributeGroups},
    values: [],
  };

  expect(selectAttributeGroupList(state)).toEqual(attributeGroups);
});

test('It should be able to dispatch an action to update the attribute group list', async () => {
  const attributeGroups = {
    marketing: {
      code: 'marketing',
      sort_order: 1,
      labels: {en_US: 'Marketing Label US', fr_FR: 'Marketing Label FR', de_DE: 'Marketing Label DE'},
    },
  };
  const dispatch = jest.fn();
  fetchAssetAttributeGroups.mockImplementation(attributeGroupFetcher => () => attributeGroups);

  await updateAttributeGroups()(dispatch);
  expect(fetchAssetAttributeGroups).toBeCalled();
  expect(dispatch).toBeCalledWith({type: 'ATTRIBUTE_GROUP_LIST_UPDATED', attributeGroups});
});
