import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import {createIdentifier as createRecordIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import {createRecord} from 'akeneoenrichedentity/domain/model/record/record';

const michelIdentifier = createRecordIdentifier('michel');
const designerIdentifier = createEnrichedEntityIdentifier('designer');
const michelCode = createCode('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const sofaIdentifier = createEnrichedEntityIdentifier('sofa');
const didierIdentifier = createRecordIdentifier('designer_didier_1');
const didierCode = createCode('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > record > domain > model --- record', () => {
  test('I can create a new record with a identifier and labels', () => {
    expect(createRecord(michelIdentifier, designerIdentifier, michelCode, michelLabels).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed record', () => {
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode);
    }).toThrow('Record expect a LabelCollection as fourth argument');
    expect(() => {
      createRecord(michelIdentifier);
    }).toThrow('Record expect an EnrichedEntityIdentifier as second argument');
    expect(() => {
      createRecord();
    }).toThrow('Record expect a RecordIdentifier as first argument');
    expect(() => {
      createRecord(12);
    }).toThrow('Record expect a RecordIdentifier as first argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode, 52);
    }).toThrow('Record expect a LabelCollection as fourth argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, didierCode, 52);
    }).toThrow('Record expect a LabelCollection as fourth argument');
    expect(() => {
      createRecord(michelIdentifier, sofaIdentifier, '12', michelLabels);
    }).toThrow('Record expect a RecordCode as third argument');
  });

  test('I can compare two record', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels).equals(
        createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels)
      )
    ).toBe(true);
    expect(
      createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels).equals(
        createRecord(michelIdentifier, designerIdentifier, michelCode, michelLabels)
      )
    ).toBe(false);
  });

  test('I can get the collection of labels', () => {
    expect(createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels).getLabelCollection()).toBe(
      didierLabels
    );
  });

  test('I can get the code of the record', () => {
    expect(createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels).getCode()).toBe(didierCode);
  });

  test('I can normalize an record', () => {
    const michelRecord = createRecord(didierIdentifier, designerIdentifier, didierCode, didierLabels);

    expect(michelRecord.normalize()).toEqual({
      identifier: 'designer_didier_1',
      enrichedEntityIdentifier: 'designer',
      code: 'didier',
      labels: {en_US: 'Didier'},
    });
  });

  test('I can get a label for the given locale', () => {
    expect(createRecord(michelIdentifier, designerIdentifier, michelCode, michelLabels).getLabel('en_US')).toBe(
      'Michel'
    );
    expect(createRecord(michelIdentifier, designerIdentifier, michelCode, michelLabels).getLabel('fr_FR')).toBe(
      '[michel]'
    );
  });
});
