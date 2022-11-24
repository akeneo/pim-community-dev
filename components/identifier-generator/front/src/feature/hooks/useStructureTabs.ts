import {useEffect, useState} from 'react';
import {StructureTabs} from '../models/structureTabs';

type StructureTabsType = {
  currentTab: StructureTabs;
  setCurrentTab: (value: StructureTabs) => void;
};
const useStructureTabs = (): StructureTabsType => {
  const [currentTab, setCurrentTab] = useState(() => {
    const localStorageItem = localStorage.getItem('identifier-generator.currentTab');
    if (localStorageItem === null) return StructureTabs.GENERAL;
    return Number(localStorageItem) as StructureTabs;
  });

  useEffect(() => {
    localStorage.setItem('identifier-generator.currentTab', JSON.stringify(currentTab));
  }, [currentTab]);

  return {currentTab, setCurrentTab};
};

export {useStructureTabs};
