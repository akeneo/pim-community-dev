import {useEffect, useState} from 'react';
import {PimVersion} from '../domain';

const Routing = require('routing');

const usePimVersion = () => {
  const [data, setData] = useState<PimVersion | null>(null);

  useEffect(() => {
    (async () => {
      // const result = await fetch(Routing.generate(''), {
      //   method: 'GET',
      // });
      // setData(await result.json());
      setData({currentVersion: 'Version: CE master Community master'});
    })();
  }, []);

  return data;
};

export {usePimVersion};
