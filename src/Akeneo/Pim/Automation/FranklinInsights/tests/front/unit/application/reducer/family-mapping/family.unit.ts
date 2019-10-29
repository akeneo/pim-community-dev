import reducer, {
  FamilyState
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping/family';
import {
  FETCHED_FAMILY_FAIL,
  FETCHED_FAMILY_SUCCESS
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family';

describe('Application > Reducer > Family Mapping > Family', () => {
  it('should return the initial state', () => {
    const expectedState: FamilyState = null;
    expect(reducer(undefined, {} as any)).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_SUCCESS', () => {
    const initialState: FamilyState = null;
    const expectedState = {
      familyCode: 'headphones',
      labels: {
        en_US: 'Headphones'
      }
    };
    expect(
      reducer(initialState, {
        type: FETCHED_FAMILY_SUCCESS,
        familyCode: 'headphones',
        labels: {en_US: 'Headphones'}
      })
    ).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_FAIL', () => {
    const initialState: FamilyState = {
      familyCode: 'headphones',
      labels: {
        en_US: 'Headphones'
      }
    };
    const expectedState = null;

    expect(
      reducer(initialState, {
        type: FETCHED_FAMILY_FAIL
      })
    ).toEqual(expectedState);
  });
});
