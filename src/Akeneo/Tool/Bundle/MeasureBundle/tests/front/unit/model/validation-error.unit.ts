import {filterErrors} from 'akeneomeasure/model/validation-error';

describe('validation error', () => {
  it('should filter errors based on the property', () => {
    const errors = [
      {propertyPath: 'code', message: 'bad code'},
      {propertyPath: 'labels', message: 'bad labels'},
    ];

    expect(filterErrors(errors, 'code')).toEqual([{propertyPath: '', message: 'bad code'}]);
  });
});
