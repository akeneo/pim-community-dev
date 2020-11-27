import {useEffect, useRef} from 'react';

const useIsMounted = () => {
  const isMounted = useRef<boolean>(true);

  useEffect(() => {
    return () => {
      isMounted.current = false;
    };
  }, []);

  return isMounted;
};

export {useIsMounted};
