import {useIsMounted} from 'akeneo-design-system';
import {useEffect, useState} from 'react';
import {Attribute, useFetchers} from '../contexts';

const useAttributes = (attributeCodes: string[]): Attribute[] => {
  const attributeFetcher = useFetchers().attribute;
  const [attributes, setAttributes] = useState<Attribute[]>([]);
  const isMounted = useIsMounted();

  useEffect(() => {
    attributeFetcher.fetchByIdentifiers(attributeCodes).then((attributes: Attribute[]) => {
      if (!isMounted()) return;

      setAttributes(attributes);
    });
  }, [attributeCodes, attributeFetcher, isMounted]);

  return attributes;
};

const useAttribute = (attributeCode: string): Attribute | null => {
  const attributeFetcher = useFetchers().attribute;
  const [attribute, setAttribute] = useState<Attribute | null>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    attributeFetcher.fetchByIdentifiers([attributeCode]).then((attributes: Attribute[]) => {
      if (!isMounted()) return;

      setAttribute(attributes[0] ?? null);
    });
  }, [attributeCode, attributeFetcher, isMounted]);

  return attribute;
};

export {useAttribute, useAttributes};
