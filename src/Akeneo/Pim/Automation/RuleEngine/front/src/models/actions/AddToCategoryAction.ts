import React from 'react';
import { AddToCategoryActionLine } from '../../pages/EditRules/components/actions/AddToCategoryActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type AddToCategoryAction = {
  module: React.FC<{ action: AddToCategoryAction } & ActionLineProps>;
  type: 'add';
  field: 'categories';
  value: string[] | null;
};

export const denormalizeAddToCategoryAction = (json: any): AddToCategoryAction | null => {
  if (json.type !== 'add') {
    return null;
  }
  if (json.field !== 'categories') {
    return null;
  }

  return {
    module: AddToCategoryActionLine,
    type: 'add',
    field: 'categories',
    value: json.value,
  };
};

export const createAddToCategoryAction: () => AddToCategoryAction = () => {
  return {
    module: AddToCategoryActionLine,
    type: 'add',
    field: 'categories',
    value: null,
  };
};
