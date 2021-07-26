import {useEffect, useState} from 'react';
import {useIsMounted} from 'akeneo-design-system';
import {Attribute} from '../models';
import {useFetchers} from '../contexts';

const useAttributes = (attributeCodes: string[]): Attribute[] => {
  const attributeFetcher = useFetchers().attribute;
  const [attributes, setAttributes] = useState<Attribute[]>([]);
  const isMounted = useIsMounted();

  useEffect(() => {
    if (0 === attributeCodes.length) {
      setAttributes([]);

      return;
    }

    attributeFetcher.fetchByIdentifiers(attributeCodes).then((attributes: Attribute[]) => {
      if (!isMounted()) return;

      setAttributes(attributes);
    });
  }, [attributeCodes, attributeFetcher, isMounted]);

  return attributes.filter(({code}) => attributeCodes.includes(code));
};

const useAttribute = (attributeCode: string): Attribute | null | false => {
  const attributeFetcher = useFetchers().attribute;
  const [attribute, setAttribute] = useState<Attribute | null | false>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    attributeFetcher.fetchByIdentifiers([attributeCode]).then((attributes: Attribute[]) => {
      if (!isMounted()) return;

      setAttribute(attributes[0] ?? false);
    });
  }, [attributeCode, attributeFetcher, isMounted]);

  if (attribute && attribute.code !== attributeCode) return null;

  return attribute;
};

export {useAttribute, useAttributes};
