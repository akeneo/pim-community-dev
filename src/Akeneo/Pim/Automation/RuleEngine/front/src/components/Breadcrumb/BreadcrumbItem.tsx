import React from 'react';
import styled from 'styled-components';

type Props = {
  href: string;
  onClick: () => void;
};

const defaultClassName = 'AknBreadcrumb-item AknBreadcrumb-item--routable';

const StyledAnchor = styled.a`
  color: ${({theme}): string => theme.color.grey120};
`;

const BreadcrumbItem: React.FC<Props> = ({children, href, onClick}) => {
  return (
    <StyledAnchor className={defaultClassName} href={href} onClick={onClick}>
      {children}
    </StyledAnchor>
  );
};

const LastBreadcrumbItem: React.FC = ({children}) => {
  return (
    <span className={`${defaultClassName} AknBreadcrumb-item--final`}>
      {children}
    </span>
  );
};

BreadcrumbItem.displayName = 'BreadcrumbItem';
LastBreadcrumbItem.displayName = 'LastBreadcrumbItem';

export {BreadcrumbItem, LastBreadcrumbItem};
