import {useState, useEffect} from 'react';

type WindowSize = {
  width: number;
  height: number;
};

const useWindowResize = (): WindowSize => {
  const [windowSize, setWindowSize] = useState<WindowSize>({width: window.innerWidth, height: window.innerHeight});

  useEffect(() => {
    const onResize = () => setWindowSize({width: window.innerWidth, height: window.innerHeight});
    window.addEventListener('resize', onResize);

    return () => window.removeEventListener('resize', onResize);
  });

  return windowSize;
};

export {useWindowResize};
