import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

const normalizedColors = {
  code: 'red',
  labels: {en_US: 'Red'},
};

describe('akeneo > attribute > domain > model > attribute > type > option --- Option', () => {
  test('I can create an Option', () => {
    expect(Option.create(createCode('red'), createLabelCollection({en_US: 'Red'})).normalize()).toEqual(
      normalizedColors
    );
  });
  test('I can get the label of an Option', () => {
    expect(Option.create(createCode('red'), createLabelCollection({en_US: 'Red'})).getLabel('en_US')).toEqual('Red');
    expect(Option.create(createCode('red'), createLabelCollection({en_US: 'Red'})).getLabel('fr_FR')).toEqual('[red]');
    expect(Option.create(createCode('red'), createLabelCollection({en_US: 'Red'})).getLabel('fr_FR', false)).toEqual(
      ''
    );
  });

  test('I can create an Option from normalized', () => {
    expect(Option.createFromNormalized(normalizedColors).normalize()).toEqual(normalizedColors);
  });
});
