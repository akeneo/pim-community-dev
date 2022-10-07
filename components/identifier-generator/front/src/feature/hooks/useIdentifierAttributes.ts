import {useQuery} from 'react-query';
import {FlattenAttribute} from '../models/flatten-attribute';

const useIdentifierAttributes: () => {
  data?: FlattenAttribute[],
  error: Error | null,
  isSuccess: boolean
} = () => {
  const getIdentifierAttributes = async () => {
    return fetch('/identifier-generator/identifier-attributes', {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {error, data, isSuccess} = useQuery<FlattenAttribute[], Error, FlattenAttribute[]>(
    'getIdentifierAttributes',
    getIdentifierAttributes,
    {
      keepPreviousData: true,
      refetchOnWindowFocus: false,
      retry: false,
    }
  );

  return {data, error, isSuccess};
};

export {useIdentifierAttributes};
