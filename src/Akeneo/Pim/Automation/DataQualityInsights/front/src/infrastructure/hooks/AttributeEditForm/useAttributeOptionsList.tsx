import {useEffect, useState} from 'react';

import {
  ATTRIBUTE_OPTIONS_LIST_LOADED,
  AttributeOptionsListStateEvent,
} from 'akeneopimstructure/js/attribute-option/hooks';

export type AttributeOptionsListState = AttributeOptionsListStateEvent;

export const initialAttributeOptionsListState = {
  attributeOptions: null,
  addExtraData: () => {},
  removeExtraData: () => {},
  getAttributeOption: () => undefined,
};

export const useAttributeOptionsList = () => {
  const [attributeOptionsState, setAttributeOptionsState] = useState<AttributeOptionsListState>(
    initialAttributeOptionsListState
  );

  useEffect(() => {
    const handleListLoaded = (event: CustomEvent<AttributeOptionsListStateEvent>) => {
      setAttributeOptionsState(event.detail);
    };

    window.addEventListener(ATTRIBUTE_OPTIONS_LIST_LOADED, handleListLoaded as EventListener);

    return () => {
      window.removeEventListener(ATTRIBUTE_OPTIONS_LIST_LOADED, handleListLoaded as EventListener);
    };
  }, []);

  return {
    ...attributeOptionsState,
  };
};
