import {useQuery} from 'react-query';
import {Attribute} from "../models/attributes";

const useIdentifierAttributes = () => {
  const getIdentifierAttributes = (): Promise<void | Attribute[]> => {
    return fetch('/identifier-generator/identifier-attributes', {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then((response: Response) => response.json().then(responseJson => responseJson));
  };

  const {data, isSuccess} = useQuery('getIdentifierAttributes', getIdentifierAttributes, {
    keepPreviousData: true,
    // TODO: check if needed
    refetchOnWindowFocus: false,
  });

  return {data, isSuccess};
};

export {useIdentifierAttributes};
