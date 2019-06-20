import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export const attributeListUpdated = (attributes: Attribute[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes: attributes.map((attribute: Attribute) => attribute.normalize())};
};

export const attributeDeleted = (deletedAttributeIdentifier: AttributeIdentifier) => {
  return {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttributeIdentifier: deletedAttributeIdentifier.normalize()};
};
