import React, {ReactElement, Children, cloneElement} from 'react';
import {BreadcrumbItemProps} from 'akeneomeasure/shared/components/BreadcrumbItem';

type BreadcrumbProps = {
  children: ReactElement<BreadcrumbItemProps> | Array<ReactElement<BreadcrumbItemProps>>;
};

const Breadcrumb = ({children}: BreadcrumbProps) => {
  const count = Children.count(children);

  return (
    <div className="AknBreadcrumb">
      {Children.map(children, (item, index) => {
        const isLast = item.props.isLast === undefined ? index === count - 1 : item.props.isLast;

        return cloneElement(item, {isLast});
      })}
    </div>
  );
};

export {Breadcrumb};
