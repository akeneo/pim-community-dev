import {getFamilyLabel} from '../../../../Infrastructure/Symfony/Resources/public/react/application/get-family-label';
import {FamilyState} from '../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping/family';
import {getLabel} from '../../../../Infrastructure/Symfony/Resources/public/react/application/get-label';

jest.mock('../../../../Infrastructure/Symfony/Resources/public/react/application/get-label');

describe('Application > GetFamilyLabel', () => {
  it('it returns the family code when there any label defined', () => {
    const familyState = null;
    const familyCode = 'my_family_code';
    const locale = 'en_US';

    const label = getFamilyLabel(familyState, familyCode, locale);

    expect(label).toEqual(familyCode);
    expect(getLabel).toHaveBeenCalledTimes(0);
  });

  it('it returns the family label for the defined locale', () => {
    const familyCode = 'my_family_code';
    const locale = 'en_US';
    const familyLabel = 'My Family';
    const familyState = {
      familyCode: `${familyCode}`,
      labels: {
        en_US: familyLabel
      }
    } as FamilyState;

    getLabel.mockReturnValue(familyLabel);

    const label = getFamilyLabel(familyState, familyCode, locale);

    expect(label).toEqual(familyLabel);
    expect(getLabel).toHaveBeenCalledWith(familyState.labels, locale, familyCode);
  });

  it('it returns the family label for the default locale (en_US)', () => {
    const familyCode = 'my_family_code';
    const familyLabel = 'My Family';
    const locale = 'en_US';
    const familyState = {
      familyCode: `${familyCode}`,
      labels: {
        en_US: familyLabel
      }
    } as FamilyState;

    getLabel.mockReturnValue(familyLabel);

    const label = getFamilyLabel(familyState, familyCode);

    expect(label).toEqual(familyLabel);
    expect(getLabel).toHaveBeenCalledWith(familyState.labels, locale, familyCode);
  });
});
