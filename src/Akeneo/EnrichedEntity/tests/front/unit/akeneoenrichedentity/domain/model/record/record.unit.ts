import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier as createRecordIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import {createRecord} from 'akeneoreferenceentity/domain/model/record/record';
import File, {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';

const michelIdentifier = createRecordIdentifier('michel');
const designerIdentifier = createReferenceEntityIdentifier('designer');
const michelCode = createCode('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const sofaIdentifier = createReferenceEntityIdentifier('sofa');
const didierIdentifier = createRecordIdentifier('designer_didier_1');
const didierCode = createCode('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});
const emptyFile = createEmptyFile();

describe('akeneo > record > domain > model --- record', () => {
  test('I can create a new record with a identifier and labels', () => {
    expect(
      createRecord(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getIdentifier()
    ).toBe(michelIdentifier);
  });

  test('I cannot create a malformed record', () => {
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode);
    }).toThrow('Record expect a LabelCollection as labelCollection argument');
    expect(() => {
      createRecord(michelIdentifier);
    }).toThrow('Record expect an ReferenceEntityIdentifier as referenceEntityIdentifier argument');
    expect(() => {
      createRecord();
    }).toThrow('Record expect a RecordIdentifier as identifier argument');
    expect(() => {
      createRecord(12);
    }).toThrow('Record expect a RecordIdentifier as identifier argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode, 52);
    }).toThrow('Record expect a LabelCollection as labelCollection argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode, didierLabels);
    }).toThrow('Record expect a File as image argument');
    expect(() => {
      createRecord(michelIdentifier, sofaIdentifier, '12', michelLabels, emptyFile);
    }).toThrow('Record expect a RecordCode as code argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode, didierLabels, emptyFile, '');
    }).toThrow('Record expect a ValueCollection as valueCollection argument');
  });

  test('I can compare two record', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createRecord(
        didierIdentifier,
        designerIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createRecord(
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
      createRecord(
        didierIdentifier,
        designerIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).equals(
        createRecord(
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
      createRecord(
        didierIdentifier,
        designerIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).getLabelCollection()
    ).toBe(didierLabels);
  });

  test('I can get the code of the record', () => {
    expect(
      createRecord(
        didierIdentifier,
        designerIdentifier,
        didierCode,
        didierLabels,
        emptyFile,
        createValueCollection([])
      ).getCode()
    ).toBe(didierCode);
  });

  test('I can normalize an record', () => {
    const michelRecord = createRecord(
      didierIdentifier,
      designerIdentifier,
      didierCode,
      didierLabels,
      emptyFile,
      createValueCollection([])
    );

    expect(michelRecord.normalize()).toEqual({
      identifier: 'designer_didier_1',
      reference_entity_identifier: 'designer',
      image: null,
      code: 'didier',
      labels: {en_US: 'Didier'},
      values: [],
    });

    expect(michelRecord.normalizeMinimal()).toEqual({
      identifier: 'designer_didier_1',
      reference_entity_identifier: 'designer',
      image: null,
      code: 'didier',
      labels: {en_US: 'Didier'},
      values: [],
    });
  });

  test('I can get a label for the given locale', () => {
    expect(
      createRecord(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('en_US')
    ).toBe('Michel');
    expect(
      createRecord(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      createRecord(
        michelIdentifier,
        designerIdentifier,
        michelCode,
        michelLabels,
        emptyFile,
        createValueCollection([])
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the value collection of the record', () => {
    expect(
      createRecord(michelIdentifier, designerIdentifier, michelCode, michelLabels, emptyFile, createValueCollection([]))
        .getValueCollection()
        .normalize()
    ).toEqual([]);
  });
});
