import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from '@akeneo-pim-community/shared';
import {Tag} from 'akeneocommunicationchannel/components/panel/card/Tag';

type DesciptionProps = {
  description: string;
  tags: Tag[];
};

const Description = (props: DesciptionProps & any): JSX.Element => (
  <StyledDescription {...props}>{props.description}</StyledDescription>
);

const StyledDescription = styled.div`
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 10px;

  ${(props: DesciptionProps & AkeneoThemedProps) => {
    if (props.tags.includes('new')) {
      return `
        color: #2b3d66;
      `;
    }

    return `
      color: ${props.theme.color.grey120}
    `;
  }}
`;

export {Description};
