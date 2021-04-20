import {useDependenciesContext} from './useDependenciesContext';
import {Translate} from '../DependenciesProvider.type';

const useTranslate = (): Translate => {
  const {translate} = useDependenciesContext();

  if (!translate) {
    throw new Error('[DependenciesContext]: Translate has not been properly initiated');
  }

  return translate;
};

export {useTranslate};
