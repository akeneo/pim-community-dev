import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';

export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes: attributes.map((attribute: Attribute) => attribute.normalize())};
};

export const attributeListDeleteAttribute = (attribute: Attribute) => {
  return {type: 'ATTRIBUTE_LIST_DELETE_ATTRIBUTE', attribute};
};
