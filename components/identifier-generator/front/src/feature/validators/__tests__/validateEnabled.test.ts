import {CONDITION_NAMES} from '../../models';
import {validateEnabled} from '../validateEnabled';

describe('validateEnabled', () => {
  it('should add violation when property is unknown', () => {
    expect(validateEnabled({type: CONDITION_NAMES.ENABLED, value: true, unknown: 'bar'}, 'conditions[0]')).toEqual([
      {
        message: 'The following properties are unknown: unknown',
        path: 'conditions[0]',
      },
    ]);
  });
});
