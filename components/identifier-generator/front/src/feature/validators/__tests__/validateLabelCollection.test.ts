import {validateLabelCollection} from '../validateLabelCollection';

describe('validateLabelCollection', () => {
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
    ).toEqual([{path: 'labels', message: 'The locale must be filled'}]);
  });

  it('should not add violation with empty label', () => {
    expect(
      validateLabelCollection(
        {
          en_US: 'My generator',
          fr_FR: '   ',
        },
        'labels'
      )
    ).toHaveLength(0);
  });

  it('should add violation with too long label', () => {
    expect(
      validateLabelCollection(
        {
          en_US: 'My generator',
          fr_FR: 'a'.repeat(300),
        },
        'labels'
      )
    ).toEqual([{path: 'labels', message: 'The label for "fr_FR" is too long: it must be 255 characters or less'}]);
  });
});
