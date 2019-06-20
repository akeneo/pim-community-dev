import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import grid, {GridState} from 'akeneoreferenceentity/application/reducer/grid';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import create, {CreateState} from 'akeneoreferenceentity/application/reducer/reference-entity/create';

export interface IndexState {
  user: UserState;
  grid: GridState<ReferenceEntity>;
  create: CreateState;
}

export default {
  user,
  grid,
  create,
};
