import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

export default interface Remover<AssetFamilyIdentifier, Identifier> {
  remove: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: Identifier) => Promise<ValidationError[] | null>;

  removeAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => Promise<ValidationError[] | null>;
}
