import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from 'akeneo-design-system';
import {Tag} from './Tag';

type TitleProps = {
  title: string;
  tags: Tag[];
};

const Title = (props: TitleProps & any): JSX.Element => <StyledTitle {...props}>{props.title}</StyledTitle>;

const StyledTitle = styled.div`
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.bigger};
  margin-top: 20px;
  margin-bottom: 10px;

  ${(props: TitleProps & AkeneoThemedProps) => {
    if (props.tags.includes('new')) {
      return `
        color: ${props.theme.color.purple100}
      `;
    } else {
      return `
        color: ${props.theme.color.grey140}
      `;
    }
  }}
`;

export {Title};
