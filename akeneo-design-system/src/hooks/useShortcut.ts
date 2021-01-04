import {Ref, useCallback, useEffect, useRef} from 'react';
import {Key} from '../shared';

/**
 * Hook to listen to keydown events on a DOM element (or document) and fire a callback
 *
 * @param key The key press to listen to
 * @param callback What callback to call when the key is pressed
 * @param externalRef This ref will be used if provided
 */
const useShortcut = <NodeType extends HTMLElement>(
  key: Key,
  callback: (args?: any) => unknown,
  externalRef: Ref<NodeType> = null
): Ref<NodeType> => {
  const memoizedCallback = useCallback(
    (event: KeyboardEvent) => {
      if (key === event.key) {
        event.stopImmediatePropagation();
        callback(event);

        return true;
      }

      return false;
    },
    [key, callback]
  );

  const internalRef = useRef<NodeType>(null);
  const ref = null === externalRef ? internalRef : externalRef;

  useEffect(() => {
    if (typeof ref !== 'function' && null !== ref.current) {
      const element = (ref.current as unknown) as NodeType;

      element.addEventListener('keydown', memoizedCallback);
      return () => element.removeEventListener('keydown', memoizedCallback);
    } else {
      window.addEventListener('keydown', memoizedCallback);
      return () => window.removeEventListener('keydown', memoizedCallback);
    }
  }, [memoizedCallback, ref]);

  return ref;
};

export {useShortcut};
