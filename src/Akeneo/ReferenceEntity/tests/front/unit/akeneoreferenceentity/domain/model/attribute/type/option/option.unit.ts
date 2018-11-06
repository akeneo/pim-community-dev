import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

const normalizedColors = {
  code: 'red',
  labelCollection: {en_US: 'Red'},
};

describe('akeneo > attribute > domain > model > attribute > type > option --- Option', () => {
  test('I can create an Option from normalized', () => {
    expect(Option.createFromNormalized(normalizedColors).normalize()).toEqual(normalizedColors);
  });
});
