import React, {ReactNode, DetailedHTMLProps, ButtonHTMLAttributes} from 'react';
import {Translate} from '../../../shared/translate';

export const SecondaryActionsDropdownButton = ({children}: {children: ReactNode}) => (
    <div className='AknSecondaryActions AknDropdown AknButtonList-item'>
        <div className='AknSecondaryActions-button' data-toggle='dropdown'></div>
        <div className='AknDropdown-menu AknDropdown-menu--right'>
            <div className='AknDropdown-menuTitle'>
                <Translate id='akeneo_connectivity.connection.secondary_actions.title' />
            </div>
            {children}
        </div>
    </div>
);

export const DropdownLink = ({
    children,
    ...props
}: DetailedHTMLProps<ButtonHTMLAttributes<HTMLButtonElement>, HTMLButtonElement>) => (
    <button type='button' className='AknDropdown-menuLink' {...props}>
        {children}
    </button>
);
