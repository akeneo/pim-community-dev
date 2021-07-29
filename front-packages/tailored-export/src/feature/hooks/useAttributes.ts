import {useEffect, useState} from 'react';
import {useIsMounted} from 'akeneo-design-system';
import {Attribute} from '../models';
import {useFetchers} from '../contexts';

const useAttributes = (attributeCodes: string[]) => {
  const attributeFetcher = useFetchers().attribute;
  const [attributes, setAttributes] = useState<Attribute[]>([]);
  const isMounted = useIsMounted();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  useEffect(() => {
    if (0 === attributeCodes.length) {
      setAttributes([]);

      return;
    }
    setIsFetching(true);

    attributeFetcher.fetchByIdentifiers(attributeCodes).then((attributes: Attribute[]) => {
      if (!isMounted()) return;

      setAttributes(attributes);
      setIsFetching(false);
    });
  }, [attributeCodes, attributeFetcher, isMounted]);

  return [isFetching, attributes.filter(({code}) => attributeCodes.includes(code))] as const;
};

const useAttribute = (attributeCode: string) => {
  const attributeFetcher = useFetchers().attribute;
  const [attribute, setAttribute] = useState<Attribute | null>(null);
  const isMounted = useIsMounted();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  useEffect(() => {
    setIsFetching(true);
    attributeFetcher.fetchByIdentifiers([attributeCode]).then((attributes: Attribute[]) => {
      if (!isMounted()) return;
      setIsFetching(false);

      setAttribute(attributes[0] ?? null);
    });
  }, [attributeCode, attributeFetcher, isMounted]);

  const currentAttribute = attribute?.code === attributeCode ? attribute : null;

  return [isFetching && !currentAttribute, currentAttribute] as const;
};

export {useAttribute, useAttributes};
