import {FamilyState} from './reducer/family-mapping/family';
import {getLabel} from './get-label';

export function getFamilyLabel(state: FamilyState, familyCode: string, locale = 'en_US'): string {
  if (state === null) {
    return familyCode;
  }

  return getLabel(state.labels, locale, familyCode);
}
