import React from 'react';
import { RemoveActionLine } from '../../pages/EditRules/components/actions/RemoveActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type RemoveAction = {
  module: React.FC<{ action: RemoveAction } & ActionLineProps>;
  field: string;
  items: string[];
  locale: string | null;
  scope: string | null;
  includeChildren: boolean | null;
};

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
