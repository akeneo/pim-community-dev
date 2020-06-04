import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from '@akeneo-pim-community/shared';

type TagProps = {
  tag: 'new' | 'updates';
};

const Tag = React.forwardRef((props: TagProps & any, ref): JSX.Element => (
  <StyledTag ref={ref} {...props}>{props.tag}</StyledTag>
));

const StyledTag = styled.div`
  text-align: center;
  text-transform: uppercase;
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.small};
  border-radius: 2px;
  margin-right: 10px;
  padding: 2px 5px;
  max-height: 24px;

  ${(props: TagProps & AkeneoThemedProps) => {
    switch (props.tag) {
      case 'new':
        return `
          background-color: #eeeaf2;
          color: #36145e;
          border: 1px solid #52267d;
        `;
      case 'updates':
        return `
          background-color: #f5fafa;
          color: #5da8a6;
          border: 1px solid #81cccc;
        `;
      default:
        return;
    }
  }}
`;

export = Tag;
