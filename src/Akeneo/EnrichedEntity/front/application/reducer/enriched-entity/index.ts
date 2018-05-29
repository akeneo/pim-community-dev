import user, {UserState} from 'akeneoenrichedentity/application/reducer/user';
import grid, {GridState} from 'akeneoenrichedentity/application/reducer/grid';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

export interface State {
  user: UserState;
  grid: GridState<EnrichedEntity>;
}

export default {
  user,
  grid,
};
