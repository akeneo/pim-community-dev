import * as React from 'react';
const router = require('pim/router');

class InvalidItemTypeError extends Error {}

export type BreadcrumbConfiguration = BreadcrumbItem[];

interface BreadcrumbItem {
  action: {
    type: string;
    route?: string;
    parameters?: {[key: string]: string | number};
  };
  label: string;
}

const renderItem = (item: BreadcrumbItem, key: number, last: boolean) => {
  switch (item.action.type) {
    case 'redirect':
      return renderRedirect(item, key, last);
    case 'display':
      return renderDisplay(item, key, last);
    default:
      throw new InvalidItemTypeError(
        `The action type "${item.action.type}" is not supported by the Breadcrumb component`
      );
  }
};

const renderRedirect = (item: BreadcrumbItem, key: number, last: boolean) => {
  const path = `#${router.generate(item.action.route, item.action.parameters ? item.action.parameters : {})}`;

  return (
    <a
      key={key}
      onClick={(event: React.MouseEvent<HTMLAnchorElement>) => {
        event.preventDefault();

        if (path !== window.location.hash) {
          router.redirect(path, {trigger: true});
        }

        return false;
      }}
      title={item.label}
      href={path}
      className={`AknBreadcrumb-item AknBreadcrumb-item--routable ${last ? 'AknBreadcrumb-item--final' : ''}`}
    >
      {item.label}
    </a>
  );
};

const renderDisplay = (item: BreadcrumbItem, key: number, last: boolean) => {
  return (
    <span key={key} className={`AknBreadcrumb-item ${last ? 'AknBreadcrumb-item--final' : ''}`} title={item.label}>
      {item.label}
    </span>
  );
};

const Breadcrumb = ({items}: {items: BreadcrumbItem[]}) => {
  return (
    <div className="AknBreadcrumb">
      {items.map((item: BreadcrumbItem, key: number) => renderItem(item, key, key === items.length - 1))}
    </div>
  );
};

export default Breadcrumb;
