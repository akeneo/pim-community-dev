import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {Query} from '../fetcher/fetcher';

export default interface Remover<AssetFamilyIdentifier, Identifier> {
  remove: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: Identifier) => Promise<ValidationError[] | null>;

  removeFromQuery: (assetFamilyIdentifier: AssetFamilyIdentifier, query: Query) => Promise<Response>;
}
