import {useState} from 'react';
import {IdentifierGenerator} from '../models';

type Response = {
  data: IdentifierGenerator[];
  isLoading: boolean;
};

const useGetGenerators = (): Response => {
  const [generators] = useState<IdentifierGenerator[]>([
    {
      code: 'code',
      conditions: [],
      delimiter: '',
      labels: {en_US: 'Generating my identifiers for SKU'},
      structure: [],
      target: 'sku',
    },
  ]);

  return {data: generators, isLoading: false};
};

export {useGetGenerators};
