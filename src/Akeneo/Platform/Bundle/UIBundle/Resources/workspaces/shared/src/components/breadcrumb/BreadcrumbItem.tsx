import React, {FC} from 'react';
import styled, {css} from 'styled-components';

type Props = {
  onClick?: () => void;
  isLast?: boolean;
};

const Item = styled.span<Props>`
  display: inline;

  &:hover {
    color: ${({theme}) => theme.color.grey120};
  }

  &:after {
    content: ' / ';
  }

  ${props =>
    props.onClick &&
    css`
      cursor: pointer;
    `};

  ${props =>
    props.isLast &&
    css`
      color: ${({theme}) => theme.color.grey100};
      &:after {
        content: '';
      }
      &:hover {
        color: ${({theme}) => theme.color.grey100};
      }
    `};
`;

const BreadcrumbItem: FC<Props> = ({children, ...props}) => {
  return <Item {...props}>{children}</Item>;
};

export {Props as BreadcrumbItemProps, BreadcrumbItem};
