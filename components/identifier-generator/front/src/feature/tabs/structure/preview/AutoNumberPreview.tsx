import React, {useMemo} from 'react';
import {AutoNumber} from '../../../models';
import {Preview} from 'akeneo-design-system';

type AutoNumberPreviewProps = {
  property: AutoNumber;
};

const AutoNumberPreview: React.FC<AutoNumberPreviewProps> = ({property}) => {
  const {digitsMin, numberMin} = property;

  const formattedNumber = useMemo(() => String(numberMin).padStart(digitsMin || 0, '0'), [digitsMin, numberMin]);

  return <Preview.Highlight>{formattedNumber}</Preview.Highlight>;
};

export {AutoNumberPreview};
