import {castReferenceEntityColumnDefinition, castSelectColumnDefinition} from '../../../src';
import 'jest-fetch-mock';
import {getComplexTableAttribute} from '../../factories';

const selectColumn = getComplexTableAttribute().table_configuration[0];
const referenceEntityColumn = getComplexTableAttribute('reference_entity').table_configuration[0];
const otherColumn = getComplexTableAttribute().table_configuration[1];

describe('TableConfiguration', () => {
  it('should not throw exceptions if cast succeed', () => {
    expect(castSelectColumnDefinition(selectColumn)).toEqual(selectColumn);
    expect(castReferenceEntityColumnDefinition(referenceEntityColumn)).toEqual(referenceEntityColumn);
  });

  it('should throw exceptions if cast fail', () => {
    expect(() => {
      castSelectColumnDefinition(otherColumn);
    }).toThrowError("Column definition should have 'select' data_type, 'number' given");
    expect(() => {
      castReferenceEntityColumnDefinition(otherColumn);
    }).toThrowError("Column definition should have 'reference_entity' data_type, 'number' given");
  });
});
