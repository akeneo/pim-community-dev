import {filterErrors} from 'akeneomeasure/model/validation-error';

describe('validation error', () => {
  it('should filter errors based on the property', () => {
    const errors = [
      {property: 'code', message: 'bad code'},
      {property: 'labels', message: 'bad labels'},
    ];

    expect(filterErrors(errors, 'code')).toEqual([{property: '', message: 'bad code'}]);
  });
});
