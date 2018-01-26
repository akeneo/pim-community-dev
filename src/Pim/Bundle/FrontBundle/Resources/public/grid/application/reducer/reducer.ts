import grid from 'pimfront/grid/domain/reducer/grid';
import user from 'pimfront/app/domain/reducer/user';
import structure from 'pimfront/app/domain/reducer/structure';
import { combineReducers } from 'redux'

export default combineReducers({
  user,
  grid,
  structure
});
