import EditionValue from '../../domain/model/asset/edition-value';
import {useValueConfig} from './useValueConfig';
import {getFieldView} from '../configuration/value';

const useInputViewGenerator = () => {
  const valueConfig = useValueConfig();

  return (value: EditionValue) => getFieldView(valueConfig, value);
};

export {useInputViewGenerator};
