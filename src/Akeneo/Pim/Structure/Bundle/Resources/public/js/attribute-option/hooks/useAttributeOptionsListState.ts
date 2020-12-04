import {useCallback, useEffect, useReducer} from 'react';
import {useSelector} from 'react-redux';

import {AttributeOptionsState} from '../store/store';
import {AttributeOption} from '../model';
import {
  ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
  AttributeOptionsExtraData,
  attributeOptionsExtraDataReducer,
  ExtraData,
  REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
} from '../reducers';

export const ATTRIBUTE_OPTIONS_LIST_LOADED = 'attribute_options_list_loaded';

export type AttributeOptionsListState = {
  attributeOptions: AttributeOption[] | null;
  extraData: AttributeOptionsExtraData;
  getAttributeOption: (code: string) => AttributeOption | undefined;
  addExtraData: (code: string, extra: ExtraData) => void;
  removeExtraData: (code: string) => void;
};

export type AttributeOptionsListStateEvent = {
  attributeOptions: AttributeOption[] | null;
  getAttributeOption: (code: string) => AttributeOption | undefined;
  addExtraData: (code: string, extra: ExtraData) => void;
  removeExtraData: (code: string) => void;
};

export const useAttributeOptionsListState = (): AttributeOptionsListState => {
  const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
  const [extraData, dispatch] = useReducer(attributeOptionsExtraDataReducer, {});

  const getAttributeOption = useCallback(
    (code: string): AttributeOption | undefined => {
      if (attributeOptions === null) {
        return undefined;
      }

      return attributeOptions.find(attributeOption => attributeOption.code === code);
    },
    [attributeOptions]
  );

  const addExtraData = useCallback(
    (code: string, extra: ExtraData) => {
      if (!getAttributeOption(code)) {
        return;
      }

      dispatch({type: ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION, code, extra});
    },
    [attributeOptions, dispatch]
  );

  const removeExtraData = useCallback(
    (code: string) => {
      if (!getAttributeOption(code)) {
        return;
      }

      dispatch({type: REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION, code});
    },
    [attributeOptions, dispatch]
  );

  useEffect(() => {
    if (attributeOptions === null) {
      return;
    }

    window.dispatchEvent(
      new CustomEvent<AttributeOptionsListStateEvent>(ATTRIBUTE_OPTIONS_LIST_LOADED, {
        detail: {
          attributeOptions,
          getAttributeOption,
          addExtraData,
          removeExtraData,
        },
      })
    );
  }, [attributeOptions, getAttributeOption, addExtraData, removeExtraData]);

  return {
    attributeOptions,
    extraData,
    getAttributeOption,
    addExtraData,
    removeExtraData,
  };
};
