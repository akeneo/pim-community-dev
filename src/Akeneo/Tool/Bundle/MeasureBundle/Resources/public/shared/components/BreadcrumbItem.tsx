import React, {PropsWithChildren} from 'react';

type BreadcrumbItemProps = {
  onClick?: () => void;
  isLast?: boolean;
};

const BreadcrumbItem = ({children: label, onClick, isLast}: PropsWithChildren<BreadcrumbItemProps>) => {
  const className = 'AknBreadcrumb-item' + (isLast ? ' AknBreadcrumb-item--final' : '');

  if (onClick) {
    return (
      <span onClick={onClick} className={className + ' AknBreadcrumb-item--routable'}>
        {label}
      </span>
    );
  }

  return <span className={className}>{label}</span>;
};

export {BreadcrumbItem, BreadcrumbItemProps};
