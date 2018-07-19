import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import Record from 'akeneoenrichedentity/domain/model/record/record';

export interface State {
  user: UserState;
  grid: GridState<Record>;
}

export default {
  user,
  grid,
};
