/* istanbul ignore file */
import {useEffect, useState} from 'react';

const FetcherRegistry = require('pim/fetcher-registry');

const useMainIdentifierCode = () => {
  const [mainIdentifierCode, setMainIdentifierCode] = useState<string | undefined>(undefined);
  useEffect(() => {
    FetcherRegistry.getFetcher('attribute')
      .getIdentifierAttribute()
      .then((mainIdentifierAttribute: {code: string}) => setMainIdentifierCode(mainIdentifierAttribute.code));
  });

  return mainIdentifierCode;
};

export {useMainIdentifierCode};
