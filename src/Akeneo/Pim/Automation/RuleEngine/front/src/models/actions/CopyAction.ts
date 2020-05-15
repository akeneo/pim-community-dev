import React from 'react';
import { CopyActionLine } from '../../pages/EditRules/components/actions/CopyActionLine';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';

export type CopyAction = {
  module: React.FC<{ action: CopyAction } & ActionLineProps>;
  fromField: string;
  fromLocale: string | null;
  fromScope: string | null;
  toField: string;
  toLocale: string | null;
  toScope: string | null;
};

export const denormalizeCopyAction = (json: any): CopyAction | null => {
  if (json.type !== 'copy') {
    return null;
  }

  return {
    module: CopyActionLine,
    fromField: json.from_field || null,
    fromLocale: json.from_locale || null,
    fromScope: json.from_scope || null,
    toField: json.to_field || null,
    toLocale: json.to_locale || null,
    toScope: json.to_scope || null,
  };
};
