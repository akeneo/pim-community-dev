import React, {useMemo} from 'react';
import {AutoNumber} from '../../../models';
import {Preview} from 'akeneo-design-system';

type AutoNumberPreviewProps = {
  property: AutoNumber;
};

const AutoNumberPreview: React.FC<AutoNumberPreviewProps> = ({property}) => {
  const {digitsMin, numberMin} = property;

  const getFormattedNumber = useMemo(() => (i: number) => String(i).padStart(digitsMin, '0'), [digitsMin]);

  return <Preview.Highlight>{getFormattedNumber(numberMin)}</Preview.Highlight>;
};

export {AutoNumberPreview};
