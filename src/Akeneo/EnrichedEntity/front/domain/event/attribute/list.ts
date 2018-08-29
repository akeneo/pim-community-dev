import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';

export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes: attributes.map((attribute: Attribute) => attribute.normalize())};
};

export const attributeDeleted = (deletedAttributeIdentifier: AttributeIdentifier) => {
  return {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttributeIdentifier: deletedAttributeIdentifier.normalize()};
};
