import {useEffect, useState} from 'react';
import {initTranslator} from '../dependencies';

const LegacyTranslationsLoader = ({children}: {children: JSX.Element}) => {
  const [loaded, setLoaded] = useState<boolean>(false);

  useEffect(() => {
    (async () => {
      await initTranslator.fetch();
      setLoaded(true);
    })();
  });

  if (!loaded) {
    return null;
  }

  return children;
};

export {LegacyTranslationsLoader};
