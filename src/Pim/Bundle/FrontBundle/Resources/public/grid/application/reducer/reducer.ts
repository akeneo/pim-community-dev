import grid from 'pimfront/grid/domain/reducer/grid';
import GridState from 'pimfront/grid/domain/model/state';
import user, { UserState } from 'pimfront/app/domain/reducer/user';
import structure, { StructureState } from 'pimfront/app/domain/reducer/structure';
import { combineReducers } from 'redux'

export interface State<GridElement> {
  user: UserState;
  grid: GridState<GridElement>;
  structure: StructureState;
};

const reducer = combineReducers({
  user,
  grid,
  structure
});

export default <Element>(state: State<Element>, action: any): State<Element> => {
  return reducer(state, action) as State<Element>;
};
