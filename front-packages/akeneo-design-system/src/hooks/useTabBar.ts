import {useCallback, useState} from 'react';

const useTabBar = (defaultTab: string) => {
  const [current, setCurrent] = useState<string>(defaultTab);
  const isCurrent = useCallback((tab: string) => tab === current, [current]);
  const switchTo = useCallback((tab: string) => setCurrent(tab), []);

  return [isCurrent, switchTo, current] as const;
};

export {useTabBar};
