import * as React from 'react';

type Props = React.PropsWithChildren<{
    breadcrumb?: React.ReactElement;
    buttons?: React.ReactElement[];
    userButtons?: React.ReactNode;
}>;

export const Header = ({children: title, breadcrumb, buttons, userButtons}: Props) => (
    <header className='AknTitleContainer'>
        <div className='AknTitleContainer-line'>
            <div className='AknTitleContainer-mainContainer'>
                <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-breadcrumbs'>{breadcrumb}</div>
                    <div className='AknTitleContainer-buttonsContainer'>
                        {userButtons}
                        {buttons && (
                            <div className='AknTitleContainer-actionsContainer AknButtonList'>
                                {buttons.map((button, index) => (
                                    <React.Fragment key={index}>{button}</React.Fragment>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-title'>{title}</div>
                    <div className='AknTitleContainer-state' />
                </div>

                <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-context AknButtonList' />
                </div>

                <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-meta AknButtonList' />
                </div>
            </div>
        </div>

        <div className='AknTitleContainer-line'>
            <div className='AknTitleContainer-navigation' />
        </div>

        <div className='AknTitleContainer-line'>
            <div className='AknTitleContainer-search' />
        </div>
    </header>
);
