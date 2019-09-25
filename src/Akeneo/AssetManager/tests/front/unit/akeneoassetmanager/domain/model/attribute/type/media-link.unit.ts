import {ConcreteMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import Identifier, {createIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  createIdentifier as createAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {AttributeType} from 'akeneoassetmanager/domain/model/attribute/minimal';

const normalizedMediaLink = {
  identifier: 'url',
  asset_family_identifier: 'designer',
  code: 'url',
  labels: {en_US: 'Url'},
  type: 'media_link',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  suffix: null,
  prefix: 'http://google.com/',
  media_type: 'image',
};

describe('akeneo > attribute > domain > model > attribute > type --- MediaLinkAttribute', () => {
  test('I can create a ConcreteMediaLinkAttribute from normalized', () => {
    expect(ConcreteMediaLinkAttribute.createFromNormalized(normalizedMediaLink).normalize()).toEqual(
      normalizedMediaLink
    );
  });

  test('I cannot create an invalid ConcreteMediaLinkAttribute', () => {
    expect(() => {
      new ConcreteMediaLinkAttribute(
        createIdentifier('designer', 'url'),
        createAssetFamilyIdentifier('designer'),
        createCode('url'),
        createLabelCollection({en_US: 'Url'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a valid Prefix as prefix');
    expect(() => {
      new ConcreteMediaLinkAttribute(
        createIdentifier('designer', 'url'),
        createAssetFamilyIdentifier('designer'),
        createCode('url'),
        createLabelCollection({en_US: 'Url'}),
        true,
        false,
        0,
        true,
        null
      );
    }).toThrow('Attribute expects a valid Suffix as suffix');
    expect(() => {
      new ConcreteMediaLinkAttribute(
        createIdentifier('designer', 'url'),
        createAssetFamilyIdentifier('designer'),
        createCode('url'),
        createLabelCollection({en_US: 'Url'}),
        true,
        false,
        0,
        true,
        null,
        'google.com'
      );
    }).toThrow('Attribute expects a valid MediaType as mediaType');
  });
});
