import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import grid, {GridState} from 'akeneoreferenceentity/application/reducer/grid';
import Record from 'akeneoreferenceentity/domain/model/record/record';

export interface State {
  user: UserState;
  grid: GridState<Record>;
}

export default {
  user,
  grid,
};
