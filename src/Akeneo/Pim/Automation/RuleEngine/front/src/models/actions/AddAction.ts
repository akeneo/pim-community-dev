import React from 'react';
import { AddActionLine } from '../../pages/EditRules/components/actions/AddActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type AddAction = {
  module: React.FC<{ action: AddAction } & ActionLineProps>;
  field: string;
  items: string[];
  locale: string | null;
  scope: string | null;
};

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
