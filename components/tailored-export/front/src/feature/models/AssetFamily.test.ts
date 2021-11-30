import {AssetFamily, getAttributeAsMainMedia} from './AssetFamily';

test('it should return the attribute as main media of an asset family', () => {
  const assetFamily: AssetFamily = {
    identifier: 'pokemons',
    attribute_as_main_media: 'media_blablabla2',
    attributes: [
      {
        identifier: 'media_blablabla2',
        type: 'media_file',
        value_per_locale: true,
        value_per_channel: true,
      },
    ],
  };
  const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily);
  expect(attributeAsMainMedia).toStrictEqual({
    identifier: 'media_blablabla2',
    type: 'media_file',
    value_per_locale: true,
    value_per_channel: true,
  });
});

test('it should throw an error if he could not find the attribute as main media', () => {
  const assetFamilyWithBadAttributeAsMainMedia: AssetFamily = {
    identifier: 'wrong_family',
    attribute_as_main_media: 'unknow_blabla',
    attributes: [
      {
        identifier: 'media_blablabla',
        type: 'wrong_type',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  };

  expect(() => {
    getAttributeAsMainMedia(assetFamilyWithBadAttributeAsMainMedia);
  }).toThrow('"unknow_blabla" attribute as main media does not exist in the family "wrong_family"');
});
