import * as React from 'react';

import {PimView} from '../../infrastructure/PimView';

type Props = React.PropsWithChildren<{
    breadcrumb?: any;
    buttons?: React.ReactElement[];
    title?: any;
}>;

export const Page = ({children, breadcrumb, buttons, title}: Props) => (
    <div className='AknDefault-contentWithColumn'>
        <div className='AknDefault-thirdColumnContainer'>
            <div className='AknDefault-thirdColumn' />
        </div>

        <div className='AknDefault-contentWithBottom'>
            <div className='AknDefault-mainContent'>
                <header className='AknTitleContainer'>
                    <div className='AknTitleContainer-line'>
                        <div className='AknTitleContainer-mainContainer'>
                            <div className='AknTitleContainer-line'>
                                <div className='AknTitleContainer-breadcrumbs'>{breadcrumb}</div>
                                <div className='AknTitleContainer-buttonsContainer'>
                                    <PimView
                                        className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                                        viewName='pim-apps-user-navigation'
                                    />
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

                {children}
            </div>
        </div>
    </div>
);
