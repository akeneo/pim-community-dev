import {getTypes} from '../../configuration/attribute';
import {useAttributeConfig} from '../useAttributeConfig';

const useAttributeTypes = () => {
  const attributeConfig = useAttributeConfig();

  return getTypes(attributeConfig);
};

export {useAttributeTypes};
