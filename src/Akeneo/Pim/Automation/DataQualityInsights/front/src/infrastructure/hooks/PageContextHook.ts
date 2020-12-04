import {PageContextState} from '../../application/state/PageContextState';

export default interface PageContextHook<S extends PageContextState> {
  (): S;
}
