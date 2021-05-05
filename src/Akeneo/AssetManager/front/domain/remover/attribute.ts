import {ValidationError} from '@akeneo-pim-community/shared';

export default interface Remover<AssetFamilyIdentifier, Identifier> {
  remove: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: Identifier) => Promise<ValidationError[] | null>;
}
