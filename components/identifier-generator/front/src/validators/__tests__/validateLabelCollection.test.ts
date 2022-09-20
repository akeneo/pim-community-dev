import {validateLabelCollection} from '../validateLabelCollection';

describe('LabelCollectionValidator', () => {
  it('should not add violation for valid label collection', () => {
    expect(
      validateLabelCollection(
        {
          en_US: 'My generator',
          fr_FR: 'Mon générateur',
        },
        'labels'
      )
    ).toHaveLength(0);
  });

  it('should add violation with empty locale', () => {
    expect(
      validateLabelCollection(
        {
          en_US: 'My generator',
          '   ': 'Mon générateur',
        },
        'labels'
      )
    ).toEqual([{path: 'labels', message: 'Locale should not be empty'}]);
  });

  it('should add violation with empty label', () => {
    expect(
      validateLabelCollection(
        {
          en_US: 'My generator',
          fr_FR: '   ',
        },
        'labels'
      )
    ).toEqual([{path: 'labels', message: 'Label for fr_FR should not be empty'}]);
  });
});
