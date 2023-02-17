import React, {useMemo} from 'react';
import {AbbreviationType, SimpleSelectProperty} from '../../../models';
import {Preview} from 'akeneo-design-system';

type Props = {
  property: SimpleSelectProperty;
};

const SimpleSelectPreview: React.FC<Props> = ({property}) => {
  const previewLabel = useMemo(() => {
    const optionCode = 'Attribute';
    if (property.process.type === AbbreviationType.TRUNCATE) {
      return optionCode.substring(0, property.process.value || 3);
    }
    return optionCode;
  }, [property.process]);

  return <Preview.Highlight>{previewLabel}</Preview.Highlight>;
};

export {SimpleSelectPreview};
