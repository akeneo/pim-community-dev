import React, {PropsWithChildren, ReactElement, ReactNode, Fragment} from 'react';

type Props = PropsWithChildren<{
    breadcrumb?: ReactElement;
    buttons?: ReactElement[];
    userButtons?: ReactNode;
    state?: ReactNode;
    imageSrc?: string;
}>;

export const PageHeader = ({children: title, breadcrumb, buttons, userButtons, state, imageSrc}: Props) => (
    <header className='AknTitleContainer'>
        <div className='AknTitleContainer-line'>
            {imageSrc && (
                <div className='AknImage AknImage--readOnly'>
                    <img className='AknImage-display' src={imageSrc} />
                </div>
            )}

            <div className='AknTitleContainer-mainContainer'>
                <div>
                    <div className='AknTitleContainer-line'>
                        <div className='AknTitleContainer-breadcrumbs'>{breadcrumb}</div>
                        <div className='AknTitleContainer-buttonsContainer'>
                            {userButtons}
                            {buttons && (
                                <div className='AknTitleContainer-actionsContainer AknButtonList'>
                                    {buttons.map((button, index) => (
                                        <Fragment key={index}>{button}</Fragment>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                    <div className='AknTitleContainer-line'>
                        <div className='AknTitleContainer-title'>{title}</div>
                        <div className='AknTitleContainer-state'>{state}</div>
                    </div>
                </div>

                {/* <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-context AknButtonList' />
                </div>

                <div className='AknTitleContainer-line'>
                    <div className='AknTitleContainer-meta AknButtonList' />
                </div> */}
            </div>
        </div>

        {/* <div className='AknTitleContainer-line'>
            <div className='AknTitleContainer-navigation' />
        </div>

        <div className='AknTitleContainer-line'>
            <div className='AknTitleContainer-search' />
        </div> */}
    </header>
);
