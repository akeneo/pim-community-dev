import React from 'react';
import styled from 'styled-components';

type TagProps = {
  tag: 'new' | 'updates';
};

export const Tag = React.forwardRef((props: TagProps & any, ref) => (
  <StyledTag ref={ref} {...props} />
));

const StyledTag = styled.div<TagProps>`
  text-align: center;
  text-transform: uppercase;
  font-size: 11px;
  border-radius: 2px;
  margin-right: 10px;
  padding: 2px 5px;
  max-height: 24px;

  ${(props: TagProps) => {
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
