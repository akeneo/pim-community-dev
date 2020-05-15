import { FallbackAction } from './FallbackAction';
import { ClearAction } from './actions/ClearAction';
import { CopyAction } from './actions/CopyAction';
import { AddAction } from './actions/AddAction';
import { RemoveAction } from './actions/RemoveAction';

export type Action = FallbackAction | ClearAction | CopyAction | AddAction | RemoveAction;
