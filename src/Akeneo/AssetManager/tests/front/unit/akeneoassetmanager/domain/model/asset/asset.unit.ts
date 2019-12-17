import {createAsset, getAssetImage} from 'akeneoassetmanager/domain/model/asset/asset';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {createValue} from 'akeneoassetmanager/domain/model/asset/value';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {denormalize as denormalizeMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {denormalize as denormalizeMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {denormalize as denormalizeTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
import {denormalize as denormalizeFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {denormalize as denormalizeMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = 'michel';
const designerIdentifier = 'designer';
const michelCode = 'michel';
const michelLabels = {en_US: 'Michel'};
const sofaIdentifier = 'sofa';
const didierIdentifier = 'designer_didier_1';
const didierCode = 'didier';
const didierLabels = {en_US: 'Didier'};
const emptyFile = [];
const channelEcommerce = 'ecommerce';
const localeEnUS = 'en_US';
const normalizedDescription = {
  identifier: 'description_1234',
  asset_family_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};
const description = denormalizeTextAttribute(normalizedDescription);
const normalizedWebsite = {
  identifier: 'website_1234',
  asset_family_identifier: 'designer',
  code: 'website',
  labels: {en_US: 'Website'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'url',
  regular_expression: null,
};
const normalizedImage = {
  identifier: 'main_image_designer_fingerprint',
  asset_family_identifier: 'designer',
  code: 'image',
  labels: {en_US: 'Image'},
  type: 'media-file',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  max_file_size: 50,
  media_type: 'image',
  allowed_extensions: [],
};
const normalizedUrl = {
  identifier: 'url_designer_fingerprint',
  asset_family_identifier: 'designer',
  code: 'url',
  labels: {en_US: 'Image'},
  type: 'media_link',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  media_type: 'image',
  prefix: 'https://my-dam.com/',
  suffix: '/500x500/small',
};

const website = denormalizeTextAttribute(normalizedWebsite);
const image = denormalizeMediaFileAttribute(normalizedImage);
const url = denormalizeMediaLinkAttribute(normalizedUrl);
const descriptionData = denormalizeTextData('a nice description');
const descriptionValue = createValue(description, 'ecommerce', 'en_US', descriptionData);
const websiteData = denormalizeTextData('');
const websiteValue = createValue(website, 'ecommerce', 'en_US', websiteData);
const urlData = denormalizeMediaLinkData('IMG_1111.jpg');
const urlValue = createValue(url, 'ecommerce', 'en_US', urlData);
const imageData = denormalizeFileData({
  filePath: '/path/to/img.jpg',
  originalFilename: 'img.jpg',
  size: 1234,
  mimeType: 'application/jpeg',
  extension: 'jpg',
});
const imageValue = createValue(image, 'ecommerce', 'en_US', imageData);
const valueCollection = createValueCollection([descriptionValue, websiteValue, imageValue, urlValue]);
const attributeAsMainMediaIdentifier = denormalizeAttributeIdentifier('main_image_designer_fingerprint');

describe('akeneo > asset > domain > model --- asset', () => {
  test('I can create a new asset with a identifier and labels', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getIdentifier()
    ).toBe(michelIdentifier);
  });

  test('I cannot create a malformed asset', () => {
    expect(() => {
      createAsset(michelIdentifier, undefined, '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier, attributeAsMainMediaIdentifier);
    }).toThrow('Code expects a string as parameter to be created');
    expect(() => {
      createAsset(undefined, '', '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(12, '', '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(
        michelIdentifier,
        sofaIdentifier,
        attributeAsMainMediaIdentifier,
        {nice: '12'},
        michelLabels,
        emptyFile
      );
    }).toThrow('Code expects a string as parameter to be created');
    expect(() => {
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        ''
      );
    }).toThrow('Asset expects a ValueCollection as valueCollection argument');
  });

  test('I can compare two asset', () => {
    const michelLabels = {en_US: 'Michel'};
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createAsset(
          didierIdentifier,
          designerIdentifier,
          attributeAsMainMediaIdentifier,
          didierCode,
          didierLabels,
          emptyFile,
          createValueCollection([])
        )
      )
    ).toBe(true);
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createAsset(
          michelIdentifier,
          designerIdentifier,
          attributeAsMainMediaIdentifier,
          michelCode,
          michelLabels,
          emptyFile,
          createValueCollection([])
        )
      )
    ).toBe(false);
  });

  test('I can get the collection of labels', () => {
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).getLabelCollection()
    ).toEqual(didierLabels);
  });

  test('I can get the code of the asset', () => {
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).getCode()
    ).toBe(didierCode);
  });

  test('I can normalize an asset', () => {
    const michelAsset = createAsset(
      didierIdentifier,
      designerIdentifier,
      attributeAsMainMediaIdentifier,
      didierCode,
      didierLabels,
      [imageValue],
      createValueCollection([])
    );

    expect(michelAsset.normalize()).toEqual({
      identifier: 'designer_didier_1',
      asset_family_identifier: 'designer',
      attribute_as_main_media_identifier: 'main_image_designer_fingerprint',
      image: [imageValue.normalize()],
      code: 'didier',
      labels: {en_US: 'Didier'},
      values: [],
    });

    expect(michelAsset.normalizeMinimal()).toEqual({
      identifier: 'designer_didier_1',
      asset_family_identifier: 'designer',
      attribute_as_main_media_identifier: 'main_image_designer_fingerprint',
      image: [imageValue.normalize()],
      code: 'didier',
      labels: {en_US: 'Didier'},
      values: [],
    });
  });

  test('I can get a label for the given locale', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('en_US')
    ).toBe('Michel');
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the value collection of the asset', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      )
        .getValueCollection()
        .normalize()
    ).toEqual([]);
  });

  test('I can get the completeness of the asset', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        attributeAsMainMediaIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        valueCollection
      ).getCompleteness(channelEcommerce, localeEnUS)
    ).toEqual({complete: 3, required: 4});
  });

  test('I can get an empty image of the asset if the asset does not have it for the channel/locale', () => {
    expect(getAssetImage(valueCollection.values, 'unknown_attribute_identifier', 'channel', 'fr_FR')).toEqual(
      createEmptyFile()
    );
  });

  test('I can get the image of the asset for the channel and locale', () => {
    expect(getAssetImage([imageValue], 'main_image_designer_fingerprint', 'ecommerce', 'en_US')).toEqual({
      extension: 'jpg',
      filePath: '/path/to/img.jpg',
      mimeType: 'application/jpeg',
      originalFilename: 'img.jpg',
      size: 1234,
    });
  });

  test('I can get the image of the asset for the channel and locale and a media link attribute', () => {
    expect(getAssetImage([urlValue], 'url_designer_fingerprint', 'ecommerce', 'en_US')).toEqual({
      extension: undefined,
      filePath: 'https://my-dam.com/IMG_1111.jpg/500x500/small',
      mimeType: undefined,
      originalFilename: 'IMG_1111.jpg',
      size: undefined,
    });
  });
});
