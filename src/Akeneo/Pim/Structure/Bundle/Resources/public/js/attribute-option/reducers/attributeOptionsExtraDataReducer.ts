import {ReactElement, Reducer} from 'react';

export const ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION = 'attribute_option/add_extra_data';
export const REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION = 'attribute_option/remove_extra_data';

export type ExtraData = string | ReactElement | HTMLElement | null | undefined;

export type AttributeOptionsExtraData = {
  [code: string]: ExtraData;
};

export type AttributeOptionsExtraDataAction = {
  type: string;
  code: string;
  extra?: ExtraData;
};

export const attributeOptionsExtraDataReducer: Reducer<AttributeOptionsExtraData, AttributeOptionsExtraDataAction> = (
  state,
  action
) => {
  switch (action.type) {
    case ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION: {
      const {code, extra} = action;

      return {
        ...state,
        [code]: extra,
      };
    }
    case REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION: {
      const {code} = action;

      return Object.keys(state).reduce((list, key) => {
        if (key === code) {
          return list;
        }

        return {
          ...list,
          [key]: state[key],
        };
      }, {});
    }
    default: {
      return state;
    }
  }
};
