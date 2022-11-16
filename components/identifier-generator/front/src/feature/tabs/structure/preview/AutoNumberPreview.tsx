import React from 'react';
import {AutoNumber} from '../../../models';
import {Preview} from 'akeneo-design-system';

type AutoNumberPreviewProps = {
  property: AutoNumber;
};

const AutoNumberPreview: React.FC<AutoNumberPreviewProps> = ({property}) => {
  const {digitsMin, numberMin} = property;

  const getFormattedNumber = (i: number) => {
    return String(Math.max(i, numberMin)).padStart(digitsMin, '0');
  };

  return <Preview.Highlight>{getFormattedNumber(123)}</Preview.Highlight>;
};

export {AutoNumberPreview};
