import referenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';

export interface ReferenceEntityPermission {
  referenceEntityIdentifier: referenceEntityIdentifier | null;
  edit: boolean;
}
