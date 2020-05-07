import {useDependenciesContext} from './useDependenciesContext';
import {Translate} from '../provider';

const useTranslate = (): Translate => {
  const {translate} = useDependenciesContext();

  if (translate) {
    return translate;
  }

  throw new Error('[DependenciesContext]: Translate has not been properly initiated');
};

export {useTranslate};
