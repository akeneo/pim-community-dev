import styled from 'styled-components';
import {LoadingPlaceholderContainer} from '@akeneo-pim-community/shared';
import React, {FC} from 'react';

const Container = styled(LoadingPlaceholderContainer)`
  display: inline-flex;
  flex-direction: row;

  > div {
    height: 12px;
    width: 75px;
  }
`;

const BreadcrumbStepSkeleton: FC = () => {
  return (
    <Container>
      <div />
    </Container>
  );
};

export {BreadcrumbStepSkeleton};
