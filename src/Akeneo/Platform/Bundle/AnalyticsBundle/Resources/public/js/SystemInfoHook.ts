import {useEffect, useState} from 'react';
import {fetchSystemInfo} from './SystemInfoFetcher';

const useSystemInfo = () => {
  const [systemInfoData, setSystemInfoData] = useState({});
  useEffect(() => {
    (async () => {
      const response = await fetchSystemInfo();
      setSystemInfoData(response);
    })();
  }, []);

  return systemInfoData;
};

export {useSystemInfo};
