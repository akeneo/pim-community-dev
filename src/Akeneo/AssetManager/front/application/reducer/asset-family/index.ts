import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import grid, {GridState} from 'akeneoassetmanager/application/reducer/grid';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import create, {CreateState} from 'akeneoassetmanager/application/reducer/asset-family/create';

export interface IndexState {
  user: UserState;
  grid: GridState<AssetFamily>;
  create: CreateState;
}

export default {
  user,
  grid,
  create,
};
