import React, {FC} from 'react';
import {CloseIcon} from 'akeneo-design-system';
import styled from 'styled-components';

type Props = {
  remove: () => void;
};

const StyledCloseIcon = styled(CloseIcon)``;

const Container = styled.div`
  line-height: 16px;
  cursor: pointer;

  ${StyledCloseIcon} {
    vertical-align: middle;
  }
`;

const RemoveItem: FC<Props> = ({remove}) => {
  return (
    <Container>
      <StyledCloseIcon size={16} onClick={remove} />
    </Container>
  );
};

export {RemoveItem};
