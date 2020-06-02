import React from 'react';
import { AddActionLine } from '../../pages/EditRules/components/actions/AddActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type AddAction = {
  module: React.FC<{ action: AddAction } & ActionLineProps>;
  type: 'add';
  items: string[];
} & ProductField;

export const denormalizeAddAction = async (
  json: any
): Promise<AddAction | null> => {
  if (json.type !== 'add') {
    return null;
  }

  return Promise.resolve({
    module: AddActionLine,
    type: 'add',
    items: json.items,
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
  });
};
