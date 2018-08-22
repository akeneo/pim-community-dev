import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';

export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes: attributes.map((attribute: Attribute) => attribute.normalize())};
};

export const attributeDeleted = (deletedAttribute: Attribute) => {
  return {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute: deletedAttribute.normalize()};
};
