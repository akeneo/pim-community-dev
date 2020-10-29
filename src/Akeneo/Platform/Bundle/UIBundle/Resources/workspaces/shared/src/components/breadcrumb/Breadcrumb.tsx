import React, {ReactElement, Children, cloneElement} from 'react';
import {BreadcrumbItemProps as ItemProps} from './BreadcrumbItem';
import styled from 'styled-components';

type Props = {
  children: ReactElement<ItemProps> | Array<ReactElement<ItemProps>>;
};

const Container = styled.div`
  color: ${({theme}) => theme.color.grey120};
  font-size: ${({theme}) => theme.fontSize.big};
  text-transform: uppercase;
  transition: color 0.2s ease-in;
`;

const Breadcrumb = ({children}: Props) => {
  const count = Children.count(children);

  return (
    <Container>
      {Children.map(children, (item, index) => {
        const isLast = item.props.isLast === undefined ? index === count - 1 : item.props.isLast;

        return cloneElement(item, {isLast});
      })}
    </Container>
  );
};

export {Breadcrumb};
