import {useIsMounted} from 'akeneo-design-system';
import {useEffect, useState} from 'react';
import {useFetchers} from '../contexts';
import {AssociationType} from '../models/AssociationType';

const useAssociationTypes = (associationTypeCodes: string[]): AssociationType[] => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationTypes, setAssociationTypes] = useState<AssociationType[]>([]);
  const isMounted = useIsMounted();

  useEffect(() => {
    if (associationTypeCodes.length === 0) {
      return;
    }

    associationTypeFetcher.fetchByCodes(associationTypeCodes).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;

      setAssociationTypes(associationTypes);
    });
  }, [associationTypeCodes, associationTypeFetcher, isMounted]);

  return associationTypes;
};

const useAssociationType = (associationTypeCode: string) => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationType, setAssociationType] = useState<AssociationType | null>(null);
  const isMounted = useIsMounted();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  useEffect(() => {
    setIsFetching(true);
    associationTypeFetcher.fetchByCodes([associationTypeCode]).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;
      setIsFetching(false);

      setAssociationType(associationTypes[0] ?? null);
    });
  }, [associationTypeCode, associationTypeFetcher, isMounted]);

  return [isFetching, associationType?.code === associationTypeCode ? associationType : null] as const;
};

export {useAssociationTypes, useAssociationType};
