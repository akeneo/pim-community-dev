import React from 'react';
import { ClearActionLine } from '../../pages/EditRules/components/actions/ClearActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type ClearAction = {
  module: React.FC<{ action: ClearAction } & ActionLineProps>;
  field: string;
  locale: string | null;
  scope: string | null;
};

export const denormalizeClearAction = (json: any): ClearAction | null => {
  if (json.type !== 'clear') {
    return null;
  }

  return {
    module: ClearActionLine,
    field: json.field,
    locale: json.locale,
    scope: json.scope,
  };
};
