import React, {FC} from 'react';
import {Placeholder} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div`
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.title};
  line-height: 34px;
  margin: 0;
  font-weight: normal;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-grow: 1;

  &:first-letter {
    text-transform: uppercase;
  }
`;

type Props = {
  showPlaceholder?: boolean;
};

const Title: FC<Props> = ({children, showPlaceholder}) => {
  return <Container>{showPlaceholder ? <Placeholder>{children}</Placeholder> : <>{children}</>}</Container>;
};

export {Title};
