import {getIcon} from '../../configuration/attribute';
import {useAttributeConfig} from '../useAttributeConfig';

const useAttributeIcon = (attributeType: string) => {
  const attributeConfig = useAttributeConfig();

  return getIcon(attributeConfig, attributeType);
};

export {useAttributeIcon};
