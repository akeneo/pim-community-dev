import React, {FC} from 'react';
import {DependenciesContext} from '@akeneo-pim-community/legacy-bridge';
import {dependencies} from './dependencies';

const DependenciesProvider: FC = ({children}) => {
    return <DependenciesContext.Provider value={dependencies}>{children}</DependenciesContext.Provider>;
};

export {DependenciesProvider};
