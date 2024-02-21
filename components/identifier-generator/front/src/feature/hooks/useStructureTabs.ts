import {useEffect, useState} from 'react';
import {GeneratorTab} from '../models';

type StructureTabsType = {
  currentTab: GeneratorTab;
  setCurrentTab: (value: GeneratorTab) => void;
};
const useStructureTabs = (): StructureTabsType => {
  const [currentTab, setCurrentTab] = useState<GeneratorTab>(() => {
    const localStorageItem = localStorage.getItem('identifier-generator.currentTab');
    if (localStorageItem === null) return GeneratorTab.GENERAL;
    const index = Object.values(GeneratorTab).findIndex(value => value === localStorageItem);
    return index !== -1 ? (localStorageItem as GeneratorTab) : GeneratorTab.GENERAL;
  });

  useEffect(() => {
    localStorage.setItem('identifier-generator.currentTab', currentTab);
  }, [currentTab]);

  return {currentTab, setCurrentTab};
};

export {useStructureTabs};
