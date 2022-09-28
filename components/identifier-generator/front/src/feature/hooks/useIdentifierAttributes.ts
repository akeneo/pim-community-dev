import {useQuery} from 'react-query';
import {Attribute} from '../models/attributes';

const useIdentifierAttributes = () => {
  const getIdentifierAttributes = async () => {
    return fetch('/identifier-generator/identifier-attributes', {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(res => {
      if (!res.ok) throw new Error(res.statusText);
      return res.json();
    });
  };

  const {error, data} = useQuery<Attribute[], Error, Attribute[]>('getIdentifierAttributes', getIdentifierAttributes, {
    keepPreviousData: true,
    refetchOnWindowFocus: false,
    retry: false,
  });

  return {data, error};
};

export {useIdentifierAttributes};
