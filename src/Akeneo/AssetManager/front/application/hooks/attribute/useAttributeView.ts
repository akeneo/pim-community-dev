import {getView} from '../../configuration/attribute';
import {useAttributeConfig} from '../useAttributeConfig';
import {Attribute} from '../../../domain/model/attribute/attribute';

const useAttributeView = (attribute: Attribute) => {
  const attributeConfig = useAttributeConfig();

  return getView(attributeConfig, attribute);
};

export {useAttributeView};
