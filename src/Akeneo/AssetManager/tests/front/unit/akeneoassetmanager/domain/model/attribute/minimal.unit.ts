import {denormalizeMinimalAttribute, MinimalConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/minimal';

const description = denormalizeMinimalAttribute({
  asset_family_identifier: 'designer',
  code: 'description',
  type: 'text',
  labels: {en_US: 'Description'},
  value_per_locale: true,
  value_per_channel: false,
});
const frontView = denormalizeMinimalAttribute({
  asset_family_identifier: 'designer',
  code: 'front_fiew',
  type: 'media_file',
  labels: {en_US: 'Front View'},
  value_per_locale: false,
  value_per_channel: true,
});

describe('akeneo > attribute > domain > model --- minimal attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getAssetFamilyIdentifier()).toEqual('designer');
    expect(description.getCode()).toEqual('description');
    expect(description.getType()).toEqual('text');
    expect(description.getLabel('en_US')).toEqual('Description');
    expect(description.getLabel('fr_fr')).toEqual('[description]');
    expect(description.getLabel('fr_fr', false)).toEqual('');
    expect(description.getLabelCollection()).toEqual({en_US: 'Description'});
  });

  test('I can create a new media file with a identifier and labels', () => {
    expect(frontView.getAssetFamilyIdentifier()).toEqual('designer');
    expect(frontView.getCode()).toEqual('front_fiew');
    expect(frontView.getType()).toEqual('media_file');
    expect(frontView.getLabel('en_US')).toEqual('Front View');
    expect(frontView.getLabel('fr_fr')).toEqual('[front_fiew]');
    expect(frontView.getLabel('fr_fr', false)).toEqual('');
    expect(frontView.getLabelCollection()).toEqual({en_US: 'Front View'});
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new MinimalConcreteAttribute('designer', 'front_view', {en_US: 'Front View'}, 'text', true);
    }).toThrow('Attribute expects a boolean as valuePerChannel');

    expect(() => {
      new MinimalConcreteAttribute('designer', 'front_view', {en_US: 'Front View'}, 'text');
    }).toThrow('Attribute expects a boolean as valuePerLocale');

    expect(() => {
      new MinimalConcreteAttribute('designer', 'front_view', {en_US: 'Front View'});
    }).toThrow('Attribute expects a string as attribute type');
  });
});
