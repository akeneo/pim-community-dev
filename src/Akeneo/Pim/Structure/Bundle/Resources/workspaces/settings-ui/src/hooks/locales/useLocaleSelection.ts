import {useContext} from 'react';
import {LocaleSelectionContext} from '../../components/datagrids/LocaleSelectionProvider';

type LocaleSelectionState = {
  selectedLocales: string[];
  selectionState: 'mixed' | boolean;
  isItemSelected: (localeCode: string) => boolean;
  onSelectionChange: (localeCode: string, value: any) => void;
  onSelectAllChange: (value: boolean) => void;
  selectedCount: number;
  updateTotalLocalesCount: (totalCount: number) => void;
};

const useLocaleSelection = (): LocaleSelectionState => {
  const context = useContext(LocaleSelectionContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'LocaleSelection' context outside Provider");
  }

  return context;
};

export {useLocaleSelection, LocaleSelectionState};
