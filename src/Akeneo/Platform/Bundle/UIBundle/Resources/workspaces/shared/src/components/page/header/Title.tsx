import React, {FC} from 'react';
import styled from 'styled-components';
import {LoadingPlaceholderContainer} from '../../LoadingPlaceholder';

const Placeholder = styled.div`
  width: 200px;
  height: 34px;
`;

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
  }
`;

type Props = {
  showPlaceholder?: boolean;
};

const Title: FC<Props> = ({children, showPlaceholder}) => {
  return (
    <Container>
      {showPlaceholder ? (
        <LoadingPlaceholderContainer>
          <Placeholder />
        </LoadingPlaceholderContainer>
      ) : (
        <>{children}</>
      )}
    </Container>
  );
};

export {Title};
