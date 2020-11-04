import React, {FC, useCallback, useEffect, useState} from 'react';

import {ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT} from '../../../constant';

const CURRENT_TAB_SESSION_KEY = 'current_form_tab';

interface TabEvent {
  currentTab: string;
}

export type TabItem = {
  id: string;
};

type TabPanelsList = {
  [id: string]: TabItem;
};

export type TabState = {
  baseId: string;
  visible: boolean;
  selectedId: string | null;
  panels: TabPanelsList;
  registerPanel: (item: any) => void;
  unregisterPanel: (id: string) => void;
};

export const useTabState = (baseId?: string): TabState => {
  const [generatedBaseId, setGeneratedBaseId] = useState<string>('');
  const [panels, setPanels] = useState<TabPanelsList>({});
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [visible, setVisible] = useState<boolean>(false);

  const registerPanel = useCallback(
    (item: TabItem) => {
      setPanels({
        ...panels,
        [item.id]: item,
      });
    },
    [panels, setPanels]
  );

  const unregisterPanel = useCallback(
    (id: string) => {
      const newPanelsList = Object.keys(panels).reduce((list, itemId) => {
        if (itemId === id) {
          return list;
        }

        return {
          ...list,
          [itemId]: panels[itemId],
        };
      }, {});

      setPanels(newPanelsList);
    },
    [panels, setPanels]
  );

  useEffect(() => {
    const baseIdentifier = baseId || '';
    setGeneratedBaseId(baseIdentifier);
  }, [baseId, setGeneratedBaseId]);

  useEffect(() => {
    const handleTabChanged = (event: CustomEvent<TabEvent>) => {
      setSelectedId(event.detail.currentTab);
    };
    window.addEventListener(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, handleTabChanged as EventListener);

    return () => {
      window.removeEventListener(ATTRIBUTE_EDIT_FORM_TAB_CHANGED_EVENT, handleTabChanged as EventListener);
    };
  }, []);

  useEffect(() => {
    setSelectedId(sessionStorage.getItem(CURRENT_TAB_SESSION_KEY));
    setVisible(true);

    return () => {
      setSelectedId(null);
      setVisible(false);
    };
  }, []);

  return {
    baseId: generatedBaseId,
    panels,
    selectedId,
    visible,
    registerPanel,
    unregisterPanel,
  };
};

type TabContentProps = TabState & {
  tabId: string;
};

const TabContent: FC<TabContentProps> = ({children, tabId, ...tabState}) => {
  const {visible, selectedId} = tabState;

  return <>{visible && selectedId && selectedId === tabId && children}</>;
};

export default TabContent;
