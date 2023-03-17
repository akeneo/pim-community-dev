import {useGetAttributeByCode} from './useGetAttributeByCode';
import {useUserContext} from '@akeneo-pim-community/shared';
import {useMemo} from 'react';
import {AttributeCode} from '../models';

const useGetAttributeLabel = (attributeCode?: AttributeCode): string => {
  const {data} = useGetAttributeByCode(attributeCode);
  const locale = useUserContext().get('catalogLocale');
  const label = useMemo(() => data?.labels[locale] || `[${attributeCode}]`, [attributeCode, data, locale]);

  return label;
};

export {useGetAttributeLabel};
