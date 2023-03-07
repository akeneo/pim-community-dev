import {useGetAttributeByCode} from './useGetAttributeByCode';
import {useUserContext} from '@akeneo-pim-community/shared';
import {useMemo} from 'react';

const useGetAttributeLabel = (attributeCode?: string): string => {
  const {data} = useGetAttributeByCode(attributeCode);
  const locale = useUserContext().get('catalogLocale');
  const label = useMemo(() => data?.labels[locale] || '', [data, locale]);

  return label;
};

export {useGetAttributeLabel};
