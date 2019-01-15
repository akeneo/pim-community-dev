import {LocalePermission} from 'akeneoreferenceentity/domain/model/permission/locale';
import {ReferenceEntityPermission} from 'akeneoreferenceentity/domain/model/permission/reference-entity';
import {NormalizedIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export interface RightState {
  locale: LocalePermission[];
  referenceEntity: ReferenceEntityPermission;
}

export default (
  state: RightState = {
    locale: [],
    referenceEntity: {referenceEntityIdentifier: '', edit: false},
  },
  action: {
    type: string;
    localePermissions: LocalePermission[];
    referenceEntityPermission: ReferenceEntityPermission;
  }
): RightState => {
  switch (action.type) {
    case 'LOCALE_PERMISSIONS_CHANGED':
      state = {...state, locale: action.localePermissions};
      break;
    case 'REFERENCE_ENTITY_PERMISSIONS_CHANGED':
      state = {...state, referenceEntity: action.referenceEntityPermission};
      break;
    default:
      break;
  }

  return state;
};

export const canEditLocale = (localesPermission: LocalePermission[], currentLocale: string) => {
  const localePermission = localesPermission.find((localePermission: LocalePermission) => {
    return localePermission.code === currentLocale;
  });

  if (undefined === localePermission) {
    return false;
  }

  return localePermission.edit;
};

export const canEditReferenceEntity = (
  referenceEntityPermission: ReferenceEntityPermission,
  referenceEntityIdentifier: NormalizedIdentifier
) => {
  if (referenceEntityPermission.referenceEntityIdentifier !== referenceEntityIdentifier) {
    return false;
  }

  return referenceEntityPermission.edit;
};
