import {createRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {createIdentifier as createRecordIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const michelIdentifier = createRecordIdentifier('michel');
const designerIdentifier = createEnrichedEntityIdentifier('designer');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const sofaIdentifier = createEnrichedEntityIdentifier('sofa');
const didierIdentifier = createRecordIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > record > domain > model --- record', () => {
  test('I can create a new record with a identifier and labels', () => {
    expect(createRecord(michelIdentifier, designerIdentifier, michelLabels).getIdentifier()).toBe(michelIdentifier);
  });

  test('I cannot create a malformed record', () => {
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier);
    }).toThrow('Record expect a LabelCollection as third argument');
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
      createRecord(michelIdentifier, designerIdentifier, 52);
    }).toThrow('Record expect a LabelCollection as third argument');
    expect(() => {
      createRecord(michelIdentifier, designerIdentifier, 52);
    }).toThrow('Record expect a LabelCollection as third argument');
  });

  test('I can compare two record', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createRecord(didierIdentifier, designerIdentifier, didierLabels).equals(
        createRecord(didierIdentifier, designerIdentifier, didierLabels)
      )
    ).toBe(true);
    expect(
      createRecord(didierIdentifier, designerIdentifier, didierLabels).equals(
        createRecord(michelIdentifier, designerIdentifier, michelLabels)
      )
    ).toBe(false);
    expect(
      createRecord(didierIdentifier, sofaIdentifier, didierLabels).equals(
        createRecord(didierIdentifier, designerIdentifier, michelLabels)
      )
    ).toBe(false);
  });

  test('I can get the collection of labels', () => {
    expect(createRecord(didierIdentifier, sofaIdentifier, didierLabels).getLabelCollection()).toBe(didierLabels);
  });

  test('I can normalize an record', () => {
    const michelRecord = createRecord(didierIdentifier, sofaIdentifier, didierLabels);

    expect(michelRecord.normalize()).toEqual({
      identifier: 'didier',
      enrichedEntityIdentifier: 'sofa',
      labels: {en_US: 'Didier'},
    });
  });

  test('I can get a label for the given locale', () => {
    expect(createRecord(michelIdentifier, designerIdentifier, michelLabels).getLabel('en_US')).toBe('Michel');
    expect(createRecord(michelIdentifier, designerIdentifier, michelLabels).getLabel('fr_FR')).toBe('[michel]');
  });
});
