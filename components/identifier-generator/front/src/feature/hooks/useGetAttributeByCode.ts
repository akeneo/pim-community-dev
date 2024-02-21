import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, AttributeNotFound, Unauthorized} from '../errors';
import {Attribute, AttributeCode} from '../models';

type Response = {data?: Attribute; error: Error | null; isLoading: boolean};

const useGetAttributeByCode = (attributeCode?: AttributeCode): Response => {
  const router = useRouter();

  const {data, isLoading, error} = useQuery<Attribute, Error, Attribute>({
    queryKey: ['getAttributeByCode', attributeCode],
    queryFn: async () => {
      const response = await fetch(router.generate('pim_enrich_attribute_rest_get', {identifier: attributeCode}), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        if (response.status === 401) throw new Unauthorized();
        if (response.status === 404) throw new AttributeNotFound();
        throw new ServerError();
      }

      return await response.json();
    },
    enabled: !!attributeCode,
  });

  return {data, isLoading, error};
};

export {useGetAttributeByCode};
