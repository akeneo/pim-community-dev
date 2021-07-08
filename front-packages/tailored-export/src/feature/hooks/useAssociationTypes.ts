import {useIsMounted} from 'akeneo-design-system';
import {useEffect, useState} from 'react';
import {useFetchers} from '../contexts';
import {AssociationType} from "../models/AssociationType";

const useAssociationTypes = (associationTypeCodes: string[]): AssociationType[] => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationTypes, setAssociationTypes] = useState<AssociationType[]>([]);
  const isMounted = useIsMounted();

  useEffect(() => {
    if (associationTypeCodes.length === 0) {
      setAssociationTypes([]);
      return;
    }

    associationTypeFetcher.fetchByCodes(associationTypeCodes).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;

      setAssociationTypes(associationTypes);
    });
  }, [associationTypeCodes, associationTypeFetcher, isMounted]);

  return associationTypes;
};

const useAssociationType = (associationTypeCode: string): AssociationType | null => {
  const associationTypeFetcher = useFetchers().associationType;
  const [associationType, setAssociationType] = useState<AssociationType | null>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    associationTypeFetcher.fetchByCodes([associationTypeCode]).then((associationTypes: AssociationType[]) => {
      if (!isMounted()) return;

      setAssociationType(associationTypes[0] ?? null);
    });
  }, [associationTypeCode, associationTypeFetcher, isMounted]);

  return associationType;
};

export {useAssociationTypes, useAssociationType};
