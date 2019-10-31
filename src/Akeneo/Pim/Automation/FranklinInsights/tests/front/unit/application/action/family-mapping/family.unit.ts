import {
  FETCHED_FAMILY_FAIL,
  FETCHED_FAMILY_SUCCESS,
  fetchedFamilyFail,
  fetchedFamilySuccess,
  fetchFamily
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family';
import {fetchFamilyLabels} from '../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/family';

jest.mock('../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/family');

describe('Application > Action > Family Mapping > Family', () => {
  const dispatch = jest.fn();

  test('It fetches a family with success', async () => {
    const familyCode = 'MyFamilyCode';
    const familyLabels = {
      en_US: 'My Family'
    };

    fetchFamilyLabels.mockResolvedValue(familyLabels);

    const promise = fetchFamily(familyCode);
    await promise(dispatch);

    expect(typeof promise).toBe('function');

    expect(dispatch).toHaveBeenCalledWith({
      type: FETCHED_FAMILY_SUCCESS,
      familyCode,
      labels: familyLabels
    });
  });
  test('It fails to fetch a family', async () => {
    const familyCode = 'MyWrongFamilyCode';

    fetchFamilyLabels.mockRejectedValue(new Error('Unexpected async error'));

    const promise = fetchFamily(familyCode);
    await promise(dispatch);

    expect(dispatch).toHaveBeenCalledWith({
      type: 'FETCHED_FAMILY_FAIL'
    });
  });
});

describe('Application > Action > Family Mapping > Family', () => {
  test('It returns the action when fetching family has failed', () => {
    const action = fetchedFamilyFail();

    expect(action.type).toBe(FETCHED_FAMILY_FAIL);
  });
  test('It returns the action when fetching family has succeed', () => {
    const familyCode = 'MyFamilyCode';
    const familyLabels = {
      en_US: 'My Family Label',
      fr_FR: 'Mon Libellé de Famille'
    };

    const action = fetchedFamilySuccess(familyCode, familyLabels);

    expect(action.type).toBe(FETCHED_FAMILY_SUCCESS);
    expect(action.familyCode).toBe(familyCode);
    expect(action.labels).toBe(familyLabels);
  });
});
