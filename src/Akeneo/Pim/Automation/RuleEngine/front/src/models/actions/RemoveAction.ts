import React from 'react';
import { RemoveActionLine } from '../../pages/EditRules/components/actions/RemoveActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';

export type RemoveAction = {
  module: React.FC<{ action: RemoveAction } & ActionLineProps>;
  items: string[];
  includeChildren: boolean | null;
} & ProductField;

export const denormalizeRemoveAction = (json: any): RemoveAction | null => {
  if (json.type !== 'remove') {
    return null;
  }

  return {
    module: RemoveActionLine,
    items: json.items,
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
    includeChildren: json.include_children || null,
  };
};
