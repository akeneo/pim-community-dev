import React, {createContext, FC} from 'react';
import {dependencies} from './dependencies';
import {Translate} from './DependenciesProvider.type';

type DependenciesContextProps = {
  translate?: Translate;
};

const DependenciesContext = createContext<DependenciesContextProps>({});

const DependenciesProvider: FC = ({children}) => {
  const value = {
    translate: dependencies.translate,
  };

  return <DependenciesContext.Provider value={value}>{children}</DependenciesContext.Provider>;
};

export {DependenciesProvider, DependenciesContextProps, DependenciesContext};
