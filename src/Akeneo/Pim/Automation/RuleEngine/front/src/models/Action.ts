import { FallbackAction } from './FallbackAction';
import { ClearAction } from './actions/ClearAction';

export type Action = FallbackAction | ClearAction;
