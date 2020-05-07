import {useDependenciesContext} from './useDependenciesContext';
import {Translate} from '../provider';

const useTranslate = (): Translate => {
  const {translate} = useDependenciesContext();

  if (!translate) {
    throw new Error('[DependenciesContext]: Translate has not been properly initiated');
  }

  return translate;
};

export {useTranslate};
