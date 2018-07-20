import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import create, {CreateState} from 'akeneoenrichedentity/application/reducer/enriched-entity/create';

export interface State {
  user: UserState;
  grid: GridState<EnrichedEntity>;
  create: CreateState;
}

export default {
  user,
  grid,
  create,
};
