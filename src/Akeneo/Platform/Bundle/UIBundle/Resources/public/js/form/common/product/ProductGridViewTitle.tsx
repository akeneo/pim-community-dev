import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  type: string;
  children: string;
};

const ProductGridViewTitle = ({type, children}: Props) => {
  const translate = useTranslate();

  return (
    <ContextContainer>
      <ViewNameContainer>{children}</ViewNameContainer>
      {type === 'public' || type === 'view' ? ` (${translate('pim_common.public_view')})` : null}
    </ContextContainer>
  );
};

const ContextContainer = styled.div`
  display: flex;
  font-size: 17px;
  font-weight: normal;
  height: 21px;
`;

const ViewNameContainer = styled.span`
  color: rgb(17, 50, 77);
  max-width: 650px;
  padding-right: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

export {ProductGridViewTitle};
