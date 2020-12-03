import React, {FC} from 'react';

import styled from 'styled-components';

const Label = styled.span`
  width: 71px;
  height: 16px;
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  font-weight: bold;
  font-style: italic;
`;

type Props = {
  rowHeader?: boolean;
};

const Cell: FC<Props> = ({children, rowHeader = false}) => {
  if (rowHeader) {
    return <Label>{children}</Label>;
  }
  return <>{children}</>;
};

export {Cell};
