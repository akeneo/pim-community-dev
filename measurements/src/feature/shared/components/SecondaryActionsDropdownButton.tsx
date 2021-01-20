import React, {ReactNode, DetailedHTMLProps, ButtonHTMLAttributes} from 'react';

const SecondaryActionsDropdownButton = ({title, children}: {title: string; children: ReactNode}) => (
  <div className="AknSecondaryActions AknDropdown AknButtonList-item">
    <div className="AknSecondaryActions-button" data-toggle="dropdown"></div>
    <div className="AknDropdown-menu AknDropdown-menu--right">
      <div className="AknDropdown-menuTitle">{title}</div>
      {children}
    </div>
  </div>
);

const DropdownLink = ({
  children,
  ...props
}: DetailedHTMLProps<ButtonHTMLAttributes<HTMLButtonElement>, HTMLButtonElement>) => (
  <button type="button" className="AknDropdown-menuLink" {...props}>
    {children}
  </button>
);

export {SecondaryActionsDropdownButton, DropdownLink};
