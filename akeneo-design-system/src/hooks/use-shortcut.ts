// import {Ref, useCallback, useEffect, useRef} from 'react';
import {Key} from 'shared/key';

// const useShortcut = <NodeType extends HTMLElement>(
//   key: Key,
//   callback: () => void,
//   externalRef: Ref<NodeType> = null
// ): Ref<NodeType> => {
//   const memoizedCallback = useCallback(
//     (event: KeyboardEvent) => {
//       console.log(event.code);
//       console.log(key);
//       if (event.code === key) {
//         callback();
//         console.log('callback');
//       }
//       key === event.code ? callback() : null;
//     },
//     [key, callback]
//   );

//   const internalRef = useRef<NodeType>(null);
//   const ref = null === externalRef ? internalRef : externalRef;

//   useEffect(() => {
//     console.log('YOOOOOOOOLOOOOO');
//     if (typeof ref !== 'function' && null !== ref.current) {
//       const element = (ref.current as unknown) as NodeType;

//       element.addEventListener('keydown', memoizedCallback);
//       return () => element.removeEventListener('keydown', memoizedCallback);
//     } else {
//       document.addEventListener('keydown', memoizedCallback);
//       return () => document.removeEventListener('keydown', memoizedCallback);
//     }
//   }, [memoizedCallback, ref]);

//   return ref;
// };

// export {useShortcut};

import {useCallback, useEffect} from 'react';

const useShortcut = (key: Key, callback: () => void) => {
  const memoizedCallback = useCallback((event: KeyboardEvent) => (key === event.code ? callback() : null), [
    key,
    callback,
  ]);

  useEffect(() => {
    document.addEventListener('keydown', memoizedCallback);
    return () => document.removeEventListener('keydown', memoizedCallback);
  }, [memoizedCallback]);
};

export {useShortcut};
