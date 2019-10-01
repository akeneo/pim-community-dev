import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {createValue} from 'akeneoassetmanager/domain/model/asset/value';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {denormalize as denormalizeTextData} from 'akeneoassetmanager/domain/model/asset/data/text';

const michelIdentifier = 'michel';
const designerIdentifier = 'designer';
const michelCode = 'michel';
const michelLabels = createLabelCollection({en_US: 'Michel'});
const sofaIdentifier = 'sofa';
const didierIdentifier = 'designer_didier_1';
const didierCode = 'didier';
const didierLabels = createLabelCollection({en_US: 'Didier'});
const emptyFile = createEmptyFile();
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
const website = denormalizeTextAttribute(normalizedWebsite);
const descriptionData = denormalizeTextData('a nice description');
const descriptionValue = createValue(description, 'ecommerce', 'en_US', descriptionData);
const websiteData = denormalizeTextData('');
const websiteValue = createValue(website, 'ecommerce', 'en_US', websiteData);
const valueCollection = createValueCollection([descriptionValue, websiteValue]);

describe('akeneo > asset > domain > model --- asset', () => {
  test('I can create a new asset with a identifier and labels', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getIdentifier()
    ).toBe(michelIdentifier);
  });

  test('I cannot create a malformed asset', () => {
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier, didierCode);
    }).toThrow('Asset expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAsset(michelIdentifier, undefined, '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier);
    }).toThrow('Code expects a string as parameter to be created');
    expect(() => {
      createAsset(undefined, '', '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(12, '', '');
    }).toThrow('Identifier expects a string as parameter to be created');
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier, didierCode, 52);
    }).toThrow('Asset expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier, didierCode, didierLabels);
    }).toThrow('Asset expects a File as image argument');
    expect(() => {
      createAsset(michelIdentifier, sofaIdentifier, {nice: '12'}, michelLabels, emptyFile);
    }).toThrow('Code expects a string as parameter to be created');
    expect(() => {
      createAsset(michelIdentifier, designerIdentifier, didierCode, didierLabels, emptyFile, '');
    }).toThrow('Asset expects a ValueCollection as valueCollection argument');
  });

  test('I can compare two asset', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createAsset(
          didierIdentifier,
          designerIdentifier,
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
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createAsset(
          michelIdentifier,
          designerIdentifier,
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
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).getLabelCollection()
    ).toBe(didierLabels);
  });

  test('I can get the code of the asset', () => {
    expect(
      createAsset(
        didierIdentifier,
        designerIdentifier,
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
      didierCode,
      didierLabels,
      emptyFile,
      createValueCollection([])
    );

    expect(michelAsset.normalize()).toEqual({
      identifier: 'designer_didier_1',
      asset_family_identifier: 'designer',
      image: null,
      code: 'didier',
      labels: {en_US: 'Didier'},
      values: [],
    });

    expect(michelAsset.normalizeMinimal()).toEqual({
      identifier: 'designer_didier_1',
      asset_family_identifier: 'designer',
      image: null,
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
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the value collection of the asset', () => {
    expect(
      createAsset(michelIdentifier, designerIdentifier, michelCode, michelLabels, emptyFile, createValueCollection([]))
        .getValueCollection()
        .normalize()
    ).toEqual([]);
  });

  test('I can get the completeness of the asset', () => {
    expect(
      createAsset(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        valueCollection
      ).getCompleteness(channelEcommerce, localeEnUS)
    ).toEqual({complete: 1, required: 2});
  });
});
