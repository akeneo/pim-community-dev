import {useIsMounted} from 'akeneo-design-system';
import {useEffect, useState} from 'react';
import {useFetchers} from '../../contexts';
import {AssociationType} from '../../models/pim/AssociationType';

const useAssociationTypes = (associationTypeCodes: string[]) => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationTypes, setAssociationTypes] = useState<AssociationType[]>([]);
  const isMounted = useIsMounted();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  useEffect(() => {
    if (associationTypeCodes.length === 0) {
      return;
    }
    setIsFetching(true);

    associationTypeFetcher.fetchByCodes(associationTypeCodes).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;

      setAssociationTypes(associationTypes);
      setIsFetching(false);
    });
  }, [associationTypeCodes, associationTypeFetcher, isMounted]);

  return [isFetching, associationTypes.filter(({code}) => associationTypeCodes.includes(code))] as const;
};

const useAssociationType = (associationTypeCode: string) => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationType, setAssociationType] = useState<AssociationType | null>(null);
  const [previousAssociationTypeCode, setPreviousAssociationTypeCode] = useState<string | null>(null);
  const isMounted = useIsMounted();
  const [isFetching, setIsFetching] = useState<boolean>(false);

  useEffect(() => {
    setIsFetching(true);
    associationTypeFetcher.fetchByCodes([associationTypeCode]).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;

      setAssociationType(associationTypes[0] ?? null);
      setIsFetching(false);
      setPreviousAssociationTypeCode(associationTypeCode);
    });
  }, [associationTypeCode, associationTypeFetcher, isMounted]);

  const currentAssociationType = associationType?.code === associationTypeCode ? associationType : null;

  return [isFetching || previousAssociationTypeCode !== associationTypeCode, currentAssociationType] as const;
};

export {useAssociationTypes, useAssociationType};
