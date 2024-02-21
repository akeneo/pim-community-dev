import React, {useMemo} from 'react';
import {AbbreviationType, FamilyProperty} from '../../../models';
import {Preview} from 'akeneo-design-system';

type Props = {
  property: FamilyProperty;
};

const FamilyCodePreview: React.FC<Props> = ({property}) => {
  const firstFamily = useMemo(() => {
    const familyCode = 'Family';
    if (property.process.type === AbbreviationType.TRUNCATE) {
      return familyCode.substring(0, property.process.value || 3);
    }
    return familyCode;
  }, [property.process]);

  return <Preview.Highlight>{firstFamily}</Preview.Highlight>;
};

export {FamilyCodePreview};
