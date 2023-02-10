import {validateFreeText} from '../validateFreeText';
import {PROPERTY_NAMES} from '../../models';

describe('validateFreeText', () => {
  it('should not add violation for valid text', () => {
    expect(validateFreeText({type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}, 'path')).toHaveLength(0);
  });

  it('should add violation for empty text', () => {
    expect(validateFreeText({type: PROPERTY_NAMES.FREE_TEXT, string: ''}, 'path')).toHaveLength(1);
  });

  it('should add violation if free text contains comma, semicolon or any space', () => {
    expect(validateFreeText({type: PROPERTY_NAMES.FREE_TEXT, string: ' AKN'}, 'freetext')).toEqual([
      {
        path: 'freetext',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);

    expect(validateFreeText({type: PROPERTY_NAMES.FREE_TEXT, string: ','}, 'freetext')).toEqual([
      {
        path: 'freetext',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);

    expect(validateFreeText({type: PROPERTY_NAMES.FREE_TEXT, string: ';'}, 'freetext')).toEqual([
      {
        path: 'freetext',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);
  });
});
