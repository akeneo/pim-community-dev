import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import create, {CreateState} from 'akeneoenrichedentity/application/reducer/record/create';

export interface State {
  user: UserState;
  grid: GridState<Record>;
  create: CreateState;
}

export default {
  user,
  grid,
  create,
};
