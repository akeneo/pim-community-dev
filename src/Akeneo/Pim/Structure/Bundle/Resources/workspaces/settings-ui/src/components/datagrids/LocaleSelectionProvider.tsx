import React, {createContext, FC, useState} from 'react';
import {Locale} from '@akeneo-pim-community/settings-ui';
import {useSelection} from 'akeneo-design-system';
import {LocaleSelectionState} from '../../hooks/locales/useLocaleSelection';

const LocaleSelectionContext = createContext<LocaleSelectionState>({
  selectedLocales: [],
  selectionState: false,
  isItemSelected: () => false,
  onSelectionChange: () => {},
  onSelectAllChange: () => {},
  selectedCount: 0,
  updateTotalLocalesCount: () => {},
});

type LocaleSelectionProviderProps = {
  locales: Locale[];
};

const LocaleSelectionProvider: FC<LocaleSelectionProviderProps> = ({locales, children}) => {
  const [totalLocalesCount, updateTotalLocalesCount] = useState(locales.length);

  const [
    selectionCollection,
    selectionState,
    isItemSelected,
    onSelectionChange,
    onSelectAllChange,
    selectedCount,
  ] = useSelection(totalLocalesCount);

  const state = {
    selectedLocales: selectionCollection.collection,
    selectionState,
    isItemSelected,
    onSelectionChange,
    onSelectAllChange,
    selectedCount,
    updateTotalLocalesCount,
  };

  return <LocaleSelectionContext.Provider value={state}>{children}</LocaleSelectionContext.Provider>;
};

export {LocaleSelectionProvider, LocaleSelectionContext};
