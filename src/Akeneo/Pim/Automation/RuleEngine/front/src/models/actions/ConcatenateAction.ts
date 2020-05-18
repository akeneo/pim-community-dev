import React from 'react';
import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

// TODO export this type in a new file and use it in the other actions
export type Field = {
    field: string;
    locale: string | null;
    scope: string | null;
}

export type ConcatenateAction = {
  module: React.FC<{ action: ConcatenateAction } & ActionLineProps>;
  from: Field[];
  to: Field;
};

export const denormalizeConcatenateAction = (json: any): ConcatenateAction | null => {
  if (json.type !== 'concatenate') {
    return null;
  }

  return {
    module: ConcatenateActionLine,
    from: json.from.map((elt: any) => ({
      field: elt.field,
      locale: elt.locale || null,
      scope: elt.scope || null,
    })),
    to: {
      field: json.to.field,
      locale: json.to.locale || null,
      scope: json.to.scope || null,
    },
  };
};
