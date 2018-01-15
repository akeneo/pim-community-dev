import Locale from 'pimfront/app/domain/model/locale'

export const localesUpdated = (locales: Locale[]) => {
  return {type: 'LOCALES_UPDATED', locales};
};
