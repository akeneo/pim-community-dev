import { Ref } from 'react';
import { Key } from '../shared';
declare const useShortcut: <NodeType extends HTMLElement>(key: Key, callback: (args?: any) => unknown, externalRef?: Ref<NodeType>) => Ref<NodeType>;
export { useShortcut };
