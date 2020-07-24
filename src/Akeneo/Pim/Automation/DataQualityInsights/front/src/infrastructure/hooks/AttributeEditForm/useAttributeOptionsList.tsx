import {useEffect, useState} from "react";

import {
  ATTRIBUTE_OPTIONS_LIST_LOADED,
  AttributeOptionsListStateEvent
} from "akeneopimstructure/js/attribute-option/hooks";

import {Attribute} from "../../../domain";

export type AttributeOptionsListState = AttributeOptionsListStateEvent;

export const initialAttributeOptionsListState = {
  attributeOptions: null,
  addExtraData: () => {},
  removeExtraData: () => {},
  getAttributeOption: () => undefined,
};

export const useAttributeOptionsList = (attribute: Attribute) => {
  const [attributeOptionsState, setAttributeOptionsState] = useState<AttributeOptionsListState>(initialAttributeOptionsListState);

  useEffect(() => {
    const handleListLoaded = (event: CustomEvent<AttributeOptionsListStateEvent>) => {
      setAttributeOptionsState(event.detail)
    };

    window.addEventListener(ATTRIBUTE_OPTIONS_LIST_LOADED, handleListLoaded as EventListener);

    return () => {
      window.removeEventListener(ATTRIBUTE_OPTIONS_LIST_LOADED, handleListLoaded as EventListener);
    };
  }, [attribute]);

  return {
    ...attributeOptionsState,
    addExtraData: async (code: string, item: any) => new Promise(() => {
      return attributeOptionsState.addExtraData(code, item);
    }),
    removeExtraData: async (code: string) => new Promise(() => {
      return attributeOptionsState.removeExtraData(code)
    })
  };
}
