import * as React from 'react';
const router = require('pim/router');

class InvalidItemTypeError extends Error {}

interface BreadcrumbItem {
  action: {
    type: string;
    route: string;
    parameters?: {[key: string]: string | number};
  };
  label: string;
}

const renderItem = (item: BreadcrumbItem, key: number) => {
  switch (item.action.type) {
    case 'redirect':
      return renderRedirect(item, key);
    default:
      throw new InvalidItemTypeError(
        `The action type "${item.action.type}" is not supported by the Breadcrumb component`
      );
  }
};

const renderRedirect = (item: BreadcrumbItem, key: number) => {
  const path = `#${router.generate(item.action.route, item.action.parameters ? item.action.parameters : {})}`;

  return (
    <a
      key={key}
      onClick={(event: any) => {
        event.preventDefault();

        if (path !== window.location.hash) {
          router.redirect(path, {trigger: true});
        }

        return false;
      }}
      href={path}
      className="AknBreadcrumb-item AknBreadcrumb-item--routable"
    >
      {item.label}
    </a>
  );
};

const Breadcrumb = ({items}: {items: BreadcrumbItem[]}) => {
  return <div className="AknBreadcrumb">{items.map((item: BreadcrumbItem, key: number) => renderItem(item, key))}</div>;
};

export default Breadcrumb;
