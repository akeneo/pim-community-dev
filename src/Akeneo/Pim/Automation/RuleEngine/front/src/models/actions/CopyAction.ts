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
    fromField: json.from_field,
    fromLocale: json.from_locale,
    fromScope: json.from_scope,
    toField: json.to_field,
    toLocale: json.to_locale,
    toScope: json.to_scope,
  };
};
