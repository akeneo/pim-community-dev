import {NormalizedIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export interface ReferenceEntityPermission {
  referenceEntityIdentifier: NormalizedIdentifier;
  edit: boolean;
}
