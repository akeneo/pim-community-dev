import { FallbackAction } from './FallbackAction';
import { ClearAction } from './actions/ClearAction';
import { CopyAction } from './actions/CopyAction';
import { AddAction } from './actions/AddAction';

export type Action = FallbackAction | ClearAction | CopyAction | AddAction;
