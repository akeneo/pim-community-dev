import {useEffect, useRef} from 'react';

const useMountedRef = () => {
  const mounted = useRef(false);

  useEffect(() => {
    mounted.current = true;
    return () => {
      mounted.current = false;
    };
  }, []);

  return mounted;
};

export {useMountedRef};
