import React from 'react';
import styled from 'styled-components';

const SpanSrOnly = styled.span`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

type Props = {
  locale: string;
  flagDescription: string;
};

const Flag: React.FC<Props> = ({locale, flagDescription}) => {
  const extractFlagFromLocale = (locale: string): string => {
    const region = locale.split('_')[locale.split('_').length - 1];

    return region.toLowerCase();
  };

  return (
    <i className={`flag flag-${extractFlagFromLocale(locale)}`}>
      <SpanSrOnly>{flagDescription}</SpanSrOnly>
    </i>
  );
};

export {Flag};
