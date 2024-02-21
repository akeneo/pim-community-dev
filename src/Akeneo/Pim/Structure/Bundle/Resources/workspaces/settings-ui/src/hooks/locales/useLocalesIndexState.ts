import {useContext} from 'react';
import {LocalesIndexContext, LocalesIndexState} from '../../components/providers';

const useLocalesIndexState = (): LocalesIndexState => {
  const context = useContext(LocalesIndexContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'LocalesIndex' context outside Provider");
  }

  return context;
};

export {useLocalesIndexState, LocalesIndexState};
