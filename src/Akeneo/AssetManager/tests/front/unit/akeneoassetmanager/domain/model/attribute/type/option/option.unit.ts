import {
  createOptionFromNormalized,
  createEmptyOption,
  getOptionLabel,
} from 'akeneoassetmanager/domain/model/attribute/type/option/option';

const normalizedColors = {
  code: 'red',
  labels: {en_US: 'Red'},
};

describe('akeneo > attribute > domain > model > attribute > type > option --- Option', () => {
  test('I can create an Option', () => {
    expect(createOptionFromNormalized({code: 'red', labels: {en_US: 'Red'}})).toEqual(normalizedColors);
  });
  test('I can get the label of an Option', () => {
    expect(getOptionLabel(createOptionFromNormalized({code: 'red', labels: {en_US: 'Red'}}), 'en_US')).toEqual('Red');
    expect(getOptionLabel(createOptionFromNormalized({code: 'red', labels: {en_US: 'Red'}}), 'fr_FR')).toEqual('[red]');
  });

  test('I can create an empty Option', () => {
    expect(createEmptyOption()).toEqual({
      code: '',
      labels: {},
    });
  });

  test('I can create an Option from normalized', () => {
    expect(createOptionFromNormalized(normalizedColors)).toEqual(normalizedColors);
  });
});
