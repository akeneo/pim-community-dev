import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  type: string;
  children: string;
};

const ProductGridContext = ({type, children}: Props) => {
  const translate = useTranslate();

  return (
    <ContextContainer>
      <ViewNameContainer>{children}</ViewNameContainer>{' '}
      {type === 'public' || type === 'view' ? `(${translate('pim_common.public_view')})` : null}
    </ContextContainer>
  );
};

const ContextContainer = styled.div`
  font-size: 17px;
  font-weight: normal;
  height: 21px;
  width: 358px;
`;

const ViewNameContainer = styled.span`
  color: rgb(17, 50, 77);
`;

export {ProductGridContext};
