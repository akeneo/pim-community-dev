import React, {PropsWithChildren, ReactElement, ReactNode, Fragment} from 'react';
import styled from 'styled-components';

type Props = PropsWithChildren<{
    breadcrumb?: ReactElement;
    buttons?: ReactElement[];
    userButtons?: ReactNode;
    state?: ReactNode;
    imageSrc?: string;
}>;

const ButtonCollection = styled.div.attrs(() => ({className: 'AknTitleContainer-actionsContainer AknButtonList'}))`
  > :not(:first-child) {
    margin-left: 10px;
  }
`;

export const PageHeader = ({children: title, breadcrumb, buttons, userButtons, state, imageSrc}: Props) => (
    <Header>
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
                                <ButtonCollection>
                                    {buttons.map((button, index) => (
                                        <Fragment key={index}>{button}</Fragment>
                                    ))}
                                </ButtonCollection>
                            )}
                        </div>
                    </div>
                    <div className='AknTitleContainer-line'>
                        <div className='AknTitleContainer-title'>{title}</div>
                        <div className='AknTitleContainer-state'>{state}</div>
                    </div>
                </div>
            </div>
        </div>
    </Header>
);

const Header = styled.header`
    position: sticky;
    top: 0;
    padding: 40px 40px 20px;
    background: white;
    z-index: 10;
`;
