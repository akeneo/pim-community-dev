import React from 'react';
import {LocaleCode} from '@akeneo-pim-community/shared';

type LocaleCodeContextState = {
  localeCode: LocaleCode;
};

export const LocaleCodeContext = React.createContext<LocaleCodeContextState>({
  localeCode: 'en_US',
});

export const useLocaleCode = () => {
  return React.useContext(LocaleCodeContext).localeCode;
};
