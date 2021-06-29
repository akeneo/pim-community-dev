import React from 'react';
import styled from 'styled-components';
import ReactMarkdown from 'react-markdown';
import {AkeneoThemedProps} from 'akeneo-design-system';
import {Tag} from './Tag';

type DesciptionProps = {
  description: string;
  tags: Tag[];
};

const Description = (props: DesciptionProps & any): JSX.Element => (
  <StyledDescription {...props}>
    <ReactMarkdown source={props.description} />
  </StyledDescription>
);

const StyledDescription = styled.div`
  display: -webkit-box;
  -webkit-line-clamp: 5;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 20px;
  line-height: 16px;

  ${(props: DesciptionProps & AkeneoThemedProps) => {
    if (props.tags.includes('new')) {
      return `
        color: ${props.theme.color.grey140};
      `;
    }

    return `
      color: ${props.theme.color.grey120};
    `;
  }}
`;

export {Description};
