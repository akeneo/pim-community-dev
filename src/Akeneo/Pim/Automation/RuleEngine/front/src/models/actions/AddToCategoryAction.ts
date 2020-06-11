import React from 'react';
import { AddToCategoryActionLine } from '../../pages/EditRules/components/actions/AddToCategoryActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ActionModuleGuesser } from "./ActionModuleGuesser";

export type AddToCategoryAction = {
  module: React.FC<{ action: AddToCategoryAction } & ActionLineProps>;
  type: 'add';
  field: 'categories';
  value: string[] | null;
};

export const getAddToCategoryModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddToCategoryActionLine);
};

export const createAddToCategoryAction: () => AddToCategoryAction = () => {
  return {
    module: AddToCategoryActionLine,
    type: 'add',
    field: 'categories',
    value: null,
  };
};
