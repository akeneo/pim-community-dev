import {
  structureReducer,
  attributeListUpdated,
  channelListUpdated,
  familyUpdated,
  selectAttributeList,
  selectChannels,
  selectLocales,
  selectFamily,
  updateChannels,
  updateFamily
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {fetchChannels} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel';
import {fetchFamily} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family';

jest.mock('pim/fetcher-registry', () => {});
fetchChannels = jest.fn();
fetchFamily = jest.fn();

test('It ignore other commands', () => {
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

  expect(newState).toMatchObject({attributes: [], channels: [], family: null});
});

test('It should update the attribute list', () => {
  const state = {attributes: [], channels: [], family: null};
  const attribute = {
    code: 'packshot',
    labels: {
      'en_US': 'packshot'
    },
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot'
  };
  const newState = structureReducer(state, {
    type: 'ATTRIBUTE_LIST_UPDATED',
    attributes: [attribute]
  });

  expect(newState).toMatchObject({attributes: [attribute], channels: [], family: null});
});

test('It should update the channel list', () => {
  const state = {attributes: [], channels: [], family: null};
  const channel = {
    code: 'ecommerce',
    labels: {
      'en_US': 'E-commerce'
    },
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: 'english',
        region: 'United States',
      }
    ]
  };

  const newState = structureReducer(state, {
    type: 'CHANNEL_LIST_UPDATED',
    channels: [channel]
  });

  expect(newState).toMatchObject({attributes:[], channels: [channel], family: null});
});

test('It should update the family', () => {
  const state = {attributes: [], channels: [], family: null};
  const family = {
    code: 'marketing',
    attributeRequirements: {
      'ecommerce': ['packshot']
    }
  };

  const newState = structureReducer(state, {
    type: 'FAMILY_UPDATED',
    family
  });

  expect(newState).toMatchObject({attributes:[], channels: [], family});
});

test('It should have an action to update the attribute list', () => {
  const attributes = [
    {
      code: 'packshot',
      labels: {
        'en_US': 'packshot'
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot'
    }
  ];
  const expectedAction = {
    type: 'ATTRIBUTE_LIST_UPDATED',
    attributes
  };

  expect(attributeListUpdated(attributes)).toMatchObject(expectedAction);
});

test('It shoud have an action to update the channel list', () => {
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        'en_US': 'E-commerce'
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        }
      ]
    }
  ];
  const expectedAction = {
    type: 'CHANNEL_LIST_UPDATED',
    channels
  };

  expect(channelListUpdated(channels)).toMatchObject(expectedAction);
});

test('It should have an action to update the family', () => {
  const family = {
    code: 'marketing',
    attributeRequirements: {
      'ecommerce': ['packshot']
    }
  };
  const expectedAction = {
    type: 'FAMILY_UPDATED',
    family
  };

  expect(familyUpdated(family)).toMatchObject(expectedAction);
});

test('It should be able to select the attribute list from the state', () => {
  const attribute = {
    code: 'packshot',
    labels: {
      'en_US': 'packshot'
    },
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot'
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [attribute], channels: [], family: null},
    values: []
  };

  expect(selectAttributeList(state)).toEqual([attribute]);
});

test('It should be able to select the channels from the state', () => {
  const channel = {
    code: 'ecommerce',
    labels: {
      'en_US': 'E-commerce'
    },
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        language: 'english',
        region: 'United States',
      }
    ]
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null},
    values: []
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
      'en_US': 'E-commerce'
    },
    locales: [locale,]
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null},
    values: []
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
      'en_US': 'E-commerce'
    },
    locales: [locale, locale]
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [channel], family: null},
    values: []
  };

  expect(selectLocales(state)).toEqual([locale]);
});

test('It should be able to select a family from the state', () => {
  const family = {
    code: 'scanner',
    attributeRequirements: {
      'ecommerce': ['packshot']
    }
  };
  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family},
    values: []
  };

  expect(selectFamily(state)).toEqual(family);
});

test('It should be able to dispatch an action to update the channels', async () => {
  const channels = [
    {
      code: 'ecommerce',
      labels: {
        'en_US': 'E-commerce'
      },
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          language: 'english',
          region: 'United States',
        }
      ]
    }
  ];
  const dispatch = jest.fn();
  fetchChannels.mockImplementation(() => channels);
  
  await updateChannels()(dispatch);
  expect(dispatch).toBeCalledWith({type: 'CHANNEL_LIST_UPDATED', channels})
});

test('It should be able to dispatch an action tu update the family', async () => {
  const familyCode = 'scanner';
  const family = {
    code: 'scanner',
    attributeRequirements: {
      'ecommerce': ['packshot']
    }
  };
  const dispatch = jest.fn();
  fetchFamily.mockImplementation(familyCode => family);

  await updateFamily(familyCode)(dispatch);
  expect(dispatch).toBeCalledWith({type: 'FAMILY_UPDATED', family});
});