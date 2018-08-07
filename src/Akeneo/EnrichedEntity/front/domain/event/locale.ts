import Locale from 'akeneoenrichedentity/domain/model/locale';

export const localesReceived = (locales: Locale[]) => {
  return {type: 'LOCALES_RECEIVED', locales};
};
