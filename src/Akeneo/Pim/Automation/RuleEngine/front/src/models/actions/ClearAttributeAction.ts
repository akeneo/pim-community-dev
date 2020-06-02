import React from 'react';
import { ActionLineProps } from '../../pages/EditRules/components/actions/ActionLineProps';
import { ProductField } from './ProductField';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { Router } from '../../dependenciesTools';
import { ClearAttributeActionLine } from '../../pages/EditRules/components/actions/ClearAttributeActionLine';

export type ClearAttributeAction = {
  module: React.FC<{ action: ClearAttributeAction } & ActionLineProps>;
  type: 'clear';
} & ProductField;

export const denormalizeClearAttributeAction = async (
  json: any,
  router: Router
): Promise<ClearAttributeAction | null> => {
  if (json.type !== 'clear') {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute) {
    return null;
  }

  return {
    module: ClearAttributeActionLine,
    type: 'clear',
    field: json.field,
    locale: json.locale || null,
    scope: json.scope || null,
  };
};

export const createClearAttributeAction: () => ClearAttributeAction = () => {
  return {
    module: ClearAttributeActionLine,
    type: 'clear',
    field: '',
    locale: null,
    scope: null,
  };
};
