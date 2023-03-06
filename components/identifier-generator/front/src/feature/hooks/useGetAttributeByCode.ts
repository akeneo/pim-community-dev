import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, AttributeNotFound, Unauthorized} from '../errors';
import {Attribute} from '../models';

type Response = {data?: Attribute; error: Error | null; isLoading: boolean};

const useGetAttributeByCode = (identifier: string): Response => {
  const router = useRouter();

  const {data, isLoading, error} = useQuery<Attribute, Error, Attribute>({
    queryKey: ['getAttributeByCode', identifier],
    queryFn: async () => {
      const response = await fetch(router.generate('pim_enrich_attribute_rest_get', {identifier}), {
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
  });

  return {data, isLoading, error};
};

export {useGetAttributeByCode};
