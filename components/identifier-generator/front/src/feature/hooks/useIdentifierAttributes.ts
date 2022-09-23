import {useQuery} from 'react-query';
import {Attribute, FlattenAttribute} from "../models/attributes";

const useIdentifierAttributes = () => {
  const getIdentifierAttributes = (): Promise<void | Attribute[]> => {
    return fetch('/configuration/identifier-generator/identifier-attributes', {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then((response: Response) =>
      response.json().then(responseJson =>
        responseJson?.map((data: FlattenAttribute): Attribute => ({
          code: data.code,
          label: Object.values(data.labels)[0],
        }))
      )
    );
  };

  const {data} = useQuery('getIdentifierAttributes', getIdentifierAttributes, {
    keepPreviousData: true,
    // TODO: check if needed
    refetchOnWindowFocus: false,
  });

  return {data};
};

export {useIdentifierAttributes};
