import React from 'react';
import { SetActionLine } from '../../pages/EditRules/components/actions/SetActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type SetAction = {
  module: React.FC<{ action: SetAction } & ActionLineProps>;
  field: string;
  locale: string | null;
  scope: string | null;
  value: any;
};

export const denormalizeSetAction = (json: any): SetAction | null => {
  if (json.type !== 'set') {
    return null;
  }

  return {
    module: SetActionLine,
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
    value: json.value,
  };
};
