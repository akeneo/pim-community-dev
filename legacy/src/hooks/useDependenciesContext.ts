import {useContext} from 'react';
import {DependenciesContextProps, DependenciesContext} from '../provider';

const useDependenciesContext = (): DependenciesContextProps => {
  const context = useContext<DependenciesContextProps>(DependenciesContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'useContext' outside Provider");
  }

  return context;
};

export {useDependenciesContext};
