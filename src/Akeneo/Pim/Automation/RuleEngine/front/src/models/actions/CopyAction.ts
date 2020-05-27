import React from 'react';
import { CopyActionLine } from '../../pages/EditRules/components/actions/CopyActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type CopyAction = {
  module: React.FC<{ action: CopyAction } & ActionLineProps>;
  type: 'copy';
  from_field: string;
  from_locale: string | null;
  from_scope: string | null;
  to_field: string;
  to_locale: string | null;
  to_scope: string | null;
};

export const denormalizeCopyAction = (json: any): CopyAction | null => {
  if (json.type !== 'copy') {
    return null;
  }

  return {
    module: CopyActionLine,
    type: 'copy',
    from_field: json.from_field,
    from_locale: json.from_locale || null,
    from_scope: json.from_scope || null,
    to_field: json.to_field,
    to_locale: json.to_locale || null,
    to_scope: json.to_scope || null,
  };
};
