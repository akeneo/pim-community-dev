import {PaginateOption} from '../models/option';
import {useQuery} from 'react-query';
import {userContext, useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';
import {AttributeCode} from '../models';
import {useGetAttributeByCode} from './useGetAttributeByCode';

const DEFAULT_LIMIT_PAGINATION = 10000;

type Props = {
  data?: PaginateOption;
  isLoading: boolean;
  error: Error | null;
};

type Params = {
  attributeCode?: AttributeCode;
  page?: number;
  search?: string;
  enabled?: boolean;
  limit?: number;
};
const useGetReferenceEntitiesRecord = ({
  attributeCode = '',
  page = 1,
  search = '',
  enabled = true,
  limit = DEFAULT_LIMIT_PAGINATION,
}: Params): Props => {
  const router = useRouter();

  const {data: attributeData} = useGetAttributeByCode(attributeCode);

  const {data, isLoading, error} = useQuery<PaginateOption, Error, PaginateOption>({
    queryKey: ['getSelectOptions', page, search, attributeData?.reference_data_name],
    queryFn: async () => {
      const url = router.generate('akeneo_reference_entities_record_index_rest', {
        referenceEntityIdentifier: attributeData?.reference_data_name,
      });
      const response = await fetch(url, {
        method: 'PUT',
        body: JSON.stringify({
          page: page - 1,
          filters: [
            {field: 'full_text', operator: '=', value: search, context: {}},
            {field: 'reference_entity', operator: '=', value: attributeData?.reference_data_name, context: {}},
          ],
          size: limit,
          channel: userContext.get('catalogScope'),
          locale: userContext.get('catalogLocale'),
        }),
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        throw new ServerError();
      }

      return await response.json();
    },
    enabled: enabled && attributeCode !== '' && !!attributeData,
  });

  return {data, isLoading, error};
};

export {useGetReferenceEntitiesRecord};
