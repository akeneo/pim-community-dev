import {useApplicationContext} from './useApplicationContext';
import {Translate} from '../provider/applicationDependenciesProvider.type';

const useTranslate = (): Translate => {
  const {translate} = useApplicationContext();
  if (translate) {
    return translate;
  }
  throw new Error(
    '[ApplicationContext]: Translate has not been properly initiated'
  );
};

export {useTranslate};
