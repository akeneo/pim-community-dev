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

export {useAssociationTypes};
