import React from 'react';
import {Preview} from 'akeneo-design-system';
import {TextTransformation} from '../../../models';
import {useTextTransformation} from '../../../hooks';

type DelimiterPreviewProps = {
  delimiter: string;
  textTransformation: TextTransformation;
};

const DelimiterPreview: React.FC<DelimiterPreviewProps> = ({delimiter, textTransformation}) => {
  const transformedDelimiter = useTextTransformation(delimiter, textTransformation);

  return <Preview.Highlight>{transformedDelimiter}</Preview.Highlight>;
};

export {DelimiterPreview};
