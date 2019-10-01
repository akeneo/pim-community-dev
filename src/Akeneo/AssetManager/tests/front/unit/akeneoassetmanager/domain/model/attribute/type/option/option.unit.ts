import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

const normalizedColors = {
  code: 'red',
  labels: {en_US: 'Red'},
};

describe('akeneo > attribute > domain > model > attribute > type > option --- Option', () => {
  test('I can create an Option', () => {
    expect(Option.create('red', createLabelCollection({en_US: 'Red'})).normalize()).toEqual(normalizedColors);
  });
  test('I can get the label of an Option', () => {
    expect(Option.create('red', createLabelCollection({en_US: 'Red'})).getLabel('en_US')).toEqual('Red');
    expect(Option.create('red', createLabelCollection({en_US: 'Red'})).getLabel('fr_FR')).toEqual('[red]');
    expect(Option.create('red', createLabelCollection({en_US: 'Red'})).getLabel('fr_FR', false)).toEqual('');
  });

  test('I can create an empty Option', () => {
    expect(Option.createEmpty().normalize()).toEqual({
      code: '',
      labels: {},
    });
  });

  test('I can create an Option from normalized', () => {
    expect(Option.createFromNormalized(normalizedColors).normalize()).toEqual(normalizedColors);
  });
});
