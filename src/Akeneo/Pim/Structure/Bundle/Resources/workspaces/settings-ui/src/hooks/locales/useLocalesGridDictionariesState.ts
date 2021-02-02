import {useContext} from 'react';
import {LocalesGridDictionariesState} from './useLocalesGridDictionaries';
import {LocalesGridDictionariesContext} from '../../components/datagrids/LocalesGridDictionariesProvider';

const useLocalesGridDictionariesState = (): LocalesGridDictionariesState => {
  const context = useContext(LocalesGridDictionariesContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'LocalesGridDictionaries' context outside Provider");
  }

  return context;
};

export {useLocalesGridDictionariesState};
