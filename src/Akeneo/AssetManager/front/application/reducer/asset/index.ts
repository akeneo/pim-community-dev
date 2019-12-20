import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import grid, {GridState} from 'akeneoassetmanager/application/reducer/grid';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';

export interface State {
  user: UserState;
  grid: GridState<ListAsset>;
}

export default {
  user,
  grid,
};
