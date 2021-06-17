import {useState, useEffect} from 'react';

type WindowsSize = {
  width: number;
  height: number;
};

const useWindowResize = (): WindowsSize => {
  const [windowSize, setWindowSize] = useState({width: window.innerWidth, height: window.innerHeight});

  useEffect(() => {
    const onResize = () => setWindowSize({width: window.innerWidth, height: window.innerHeight});
    window.addEventListener('resize', onResize);

    return () => window.removeEventListener('resize', onResize);
  });

  return windowSize;
};

export {useWindowResize};
