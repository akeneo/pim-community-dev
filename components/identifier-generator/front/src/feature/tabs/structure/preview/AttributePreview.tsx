import React, {useMemo} from 'react';
import {AbbreviationType, RefEntityProperty, SimpleSelectProperty} from '../../../models';
import {Preview} from 'akeneo-design-system';

type Props = {
  property: SimpleSelectProperty | RefEntityProperty;
};

const AttributePreview: React.FC<Props> = ({property}) => {
  const previewLabel = useMemo(() => {
    if (property.process.type === AbbreviationType.TRUNCATE) {
      return property.attributeCode?.substring(0, property.process.value || 3);
    }
    return property?.attributeCode;
  }, [property]);

  return <Preview.Highlight>{previewLabel}</Preview.Highlight>;
};

export {AttributePreview};
