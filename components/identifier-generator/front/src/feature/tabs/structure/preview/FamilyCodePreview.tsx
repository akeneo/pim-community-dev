import React, {useMemo} from 'react';
import {AbbreviationType, FamilyCodeProperty} from '../../../models';
import {useGetFamilies} from '../../../hooks/useGetFamilies';
import {Preview} from 'akeneo-design-system';

type Props = {
  property: FamilyCodeProperty;
};

const FamilyCodePreview: React.FC<Props> = ({property}) => {
  const {data, isLoading} = useGetFamilies({page:1, search: ''});
  const firstFamily: string = useMemo(() => {
    if (isLoading) {
      return '';
    }
    const familyCode = data?.[0]?.code || '';
    if (property.process.type === AbbreviationType.TRUNCATE) {
      return familyCode.substring(0, property.process.value || 3);
    }
    return familyCode;
  }, [isLoading, data, property.process.type, property.process.value]);

  return <Preview.Highlight>{firstFamily}</Preview.Highlight>;
};

export {FamilyCodePreview};
