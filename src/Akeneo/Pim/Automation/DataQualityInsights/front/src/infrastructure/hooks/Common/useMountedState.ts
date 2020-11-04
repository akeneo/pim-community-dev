import {useCallback, useEffect, useRef} from 'react';

type MountedState = {
  isMounted(): boolean;
};

export const useMountedState = (): MountedState => {
  const mountedRef = useRef<boolean>(false);

  useEffect(() => {
    mountedRef.current = true;

    return () => {
      mountedRef.current = false;
    };
  }, []);

  const isMounted = useCallback(() => {
    return mountedRef.current;
  }, []);

  return {
    isMounted,
  };
};
