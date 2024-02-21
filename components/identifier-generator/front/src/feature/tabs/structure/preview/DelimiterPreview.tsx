import React from 'react';
import {Preview} from 'akeneo-design-system';

type DelimiterPreviewProps = {
  delimiter: string;
};

const DelimiterPreview: React.FC<DelimiterPreviewProps> = ({delimiter}) => {
  return <Preview.Highlight>{delimiter}</Preview.Highlight>;
};

export {DelimiterPreview};
