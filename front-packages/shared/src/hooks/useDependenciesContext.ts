import {useContext} from 'react';
import {DependenciesContextProps, DependenciesContext} from '../DependenciesContext';

const useDependenciesContext = () => useContext<DependenciesContextProps>(DependenciesContext);

export {useDependenciesContext};
