import React from 'react';
import { AddActionLine } from '../../pages/EditRules/components/actions/AddActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type AddAction = {
  module: React.FC<{ action: AddAction } & ActionLineProps>;
  items: string[];
} & ProductField;

export const denormalizeAddAction = (json: any): AddAction | null => {
  if (json.type !== 'add') {
    return null;
  }

  return {
    module: AddActionLine,
    items: json.items,
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
  };
};
