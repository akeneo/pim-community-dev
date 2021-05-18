import {useState} from 'react';

const useTabBar = (defaultTab: string) => {
  const [current, setCurrent] = useState<string>(defaultTab);
  const isCurrent = (tab: string) => tab === current;
  const switchTo = (tab: string) => () => setCurrent(tab);

  return [isCurrent, switchTo] as const;
};

export {useTabBar};
