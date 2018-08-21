import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';

export default interface Remover {
  remove: (attributeIdentifier: AttributeIdentifier) => Promise<void>;
}
