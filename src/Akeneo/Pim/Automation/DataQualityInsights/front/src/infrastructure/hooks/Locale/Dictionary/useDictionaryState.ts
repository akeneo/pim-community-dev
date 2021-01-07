import {useContext} from 'react';
import {DictionaryState} from './useLocaleDictionary';
import {DictionaryContext} from '../../../../application/component/Locale/DictionaryProvider';

const useDictionaryState = (): DictionaryState => {
  const context = useContext(DictionaryContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'DictionaryIndex' context outside Provider");
  }

  return context;
};

export {useDictionaryState};
