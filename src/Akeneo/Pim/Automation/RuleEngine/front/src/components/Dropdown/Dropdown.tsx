import React from 'react';

type Props = {
  title: string;
};

const Dropdown: React.FC<Props> = ({title, children}) => {
  return (
    <div className='AknSecondaryActions AknDropdown AknButtonList-item'>
      <div className='AknSecondaryActions-button' data-toggle='dropdown'></div>
      <div className='AknDropdown-menu AknDropdown-menu--right'>
        <div className='AknDropdown-menuTitle'>{title}</div>
        {children}
      </div>
    </div>
  );
};

export {Dropdown};
