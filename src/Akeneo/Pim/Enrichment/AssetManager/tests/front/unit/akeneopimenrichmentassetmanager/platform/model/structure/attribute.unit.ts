import {getAttributeLabel} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';

test('It should get the attribute label for the locale given', () => {
  const attribute = {
    code: 'packshot',
    labels: {
      en_US: 'Packshot',
    },
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot',
  };
  const locale = 'en_US';

  expect(getAttributeLabel(attribute, locale)).toEqual('Packshot');
});

test('It should get the code as a fallback when the attribute has no labels for the current locale', () => {
  const attribute = {
    code: 'packshot',
    labels: {},
    group: 'marketing',
    isReadOnly: false,
    referenceDataName: 'packshot',
  };
  const locale = 'en_US';

  expect(getAttributeLabel(attribute, locale)).toEqual('[packshot]');
});
