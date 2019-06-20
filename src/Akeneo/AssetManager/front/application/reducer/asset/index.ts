import user, {UserState} from 'akeneoassetmanager/application/reducer/user';
import grid, {GridState} from 'akeneoassetmanager/application/reducer/grid';
import Asset from 'akeneoassetmanager/domain/model/asset/asset';

export interface State {
  user: UserState;
  grid: GridState<Asset>;
}

export default {
  user,
  grid,
};
