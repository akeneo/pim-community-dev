import {isValueComplete, hasValues} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';

test('The value is complete when the family is null', () => {
  const value = {
    attribute: {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: ['iphone'],
    editable: true,
  };
  const channel = 'ecommerce';

  expect(isValueComplete(value, null, channel)).toEqual(true);
});

test('The value is complete when the required family attributes are not defined for the channel provided', () => {
  const value = {
    attribute: {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: ['iphone'],
    editable: true,
  };
  const family = {
    code: 'scanners',
    attributeRequirements: {},
  };
  const channel = 'ecommerce';

  expect(isValueComplete(value, family, channel)).toEqual(true);
});

test('The value is complete when the attribute code of the value is not a required family attribute', () => {
  const value = {
    attribute: {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: [],
    editable: true,
  };
  const family = {
    code: 'scanners',
    attributeRequirements: {ecommerce: ['notices']},
  };
  const channel = 'ecommerce';

  expect(isValueComplete(value, family, channel)).toEqual(true);
});

test('The value is complete when the attribute code of the value is a required family attribute and has at least one data', () => {
  const value = {
    attribute: {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: ['iphone'],
    editable: true,
  };
  const family = {
    code: 'scanners',
    attributeRequirements: {ecommerce: ['packshot']},
  };
  const channel = 'ecommerce';

  expect(isValueComplete(value, family, channel)).toEqual(true);
});

test('The value is not complete when the attribute code of the value is a required family attribute and has no data', () => {
  const value = {
    attribute: {
      code: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'packshot',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: [],
    editable: true,
  };
  const family = {
    code: 'scanners',
    attributeRequirements: {ecommerce: ['packshot']},
  };
  const channel = 'ecommerce';

  expect(isValueComplete(value, family, channel)).toEqual(false);
});

test('It should check if the value collection is empty', () => {
  const values = [
    {
      attribute: {
        code: 'packshot',
        labels: {
          en_US: 'Packshot',
        },
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'packshot',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: [],
      editable: true,
    },
  ];
  expect(hasValues(values)).toEqual(true);
  expect(hasValues([])).toEqual(false);
});
