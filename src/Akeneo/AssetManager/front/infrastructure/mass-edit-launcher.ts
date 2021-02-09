import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import AssetFamilyIdentifier from '../domain/model/asset-family/identifier';
import {normalizeUpdaterCollection, Updater} from '../application/component/library/MassEdit/model/updater';
import {Query} from '../domain/fetcher/fetcher';

export interface MassEditLauncher {
  validate (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    updaterCollection: Updater[]
  ): Promise<ValidationError[]>;

  launch(
    _assetFamilyIdentifier: AssetFamilyIdentifier,
    _query: Query,
    updaterCollection: Updater[]
  ): Promise<void>;
}

export class MassEditLauncherImplementation implements MassEditLauncher {
  constructor() {
    Object.freeze(this);
  }

  async validate(_assetFamilyIdentifier: AssetFamilyIdentifier, updaterCollection: Updater[]): Promise<ValidationError[]> {
    const _normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    return Promise.resolve([]);
  }

  async launch(
    _assetFamilyIdentifier: AssetFamilyIdentifier,
    _query: Query,
    updaterCollection: Updater[]
  ) : Promise<void> {
    const _normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    return Promise.resolve();
  }
}

export default new MassEditLauncherImplementation();
