import React, {useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';

type Result = {count: number; isLoading: boolean; error: null | string};

const useGetIdentifierAttributesCount = (): Result => {
  const Router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [count, setCount] = useState(0);
  const [error, setError] = useState<null | string>(null);

  React.useEffect(() => {
    setIsLoading(true);
    fetch(
      Router.generate('pim_enrich_attribute_rest_index', {
        types: ['pim_catalog_identifier'],
      })
    ).then(response => {
      response
        .json()
        .then(data => {
          setCount(data.length);
          setIsLoading(false);
        })
        .catch(reason => setError(reason));
    });
  }, []);

  return {count, isLoading, error};
};

export {useGetIdentifierAttributesCount};
