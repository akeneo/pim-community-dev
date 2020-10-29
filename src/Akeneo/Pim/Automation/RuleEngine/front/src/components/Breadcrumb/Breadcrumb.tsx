import React from 'react';

const Breadcrumb: React.FC = ({children}) => {
  return <nav className='AknBreadcrumb'>{children}</nav>;
};

Breadcrumb.displayName = 'Breadcrumb';

export {Breadcrumb};
