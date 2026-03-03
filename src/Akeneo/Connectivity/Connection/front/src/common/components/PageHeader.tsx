import React, {PropsWithChildren, ReactElement, ReactNode, Fragment, cloneElement} from 'react';
import styled from 'styled-components';
import {SandboxHelper} from '@akeneo-pim-community/shared';

type Props = PropsWithChildren<{
    breadcrumb?: ReactElement;
    buttons?: ReactElement[];
    userButtons?: ReactNode;
    state?: ReactNode;
    imageSrc?: string;
    imageIllustration?: ReactElement;
    tag?: ReactNode;
    contextContainer?: ReactElement;
}>;

const ButtonCollection = styled.div.attrs(() => ({className: 'AknTitleContainer-actionsContainer AknButtonList'}))`
    > :not(:last-child) {
        margin-right: 10px;
    }
`;

const AknTitleContainerBreadcrumbs = styled.div.attrs(() => ({className: 'AknTitleContainer-breadcrumbs'}))`
    min-height: 32px;
`;

const Header = styled.header`
    position: sticky;
    top: 0;
    padding: 40px 40px 20px;
    background: white;
    z-index: 10;

    .AknImage-display {
        max-width: 100%;
    }
`;

const IllustrationContainer = styled.div`
    position: relative;
    width: 142px;
    height: 142px;
    border: 1px solid #ccd1d8;
    margin-right: 20px;
    border-radius: 4px;
    display: flex;
    overflow: hidden;
    flex-basis: 142px;
    flex-shrink: 0;

    & > * {
        width: 100%;
    }
`;

export const PageHeader = ({
    children: title,
    breadcrumb,
    buttons,
    userButtons,
    state,
    imageSrc,
    imageIllustration,
    tag,
    contextContainer,
}: Props) => (
    <>
        <SandboxHelper />
        <Header>
            <div className='AknTitleContainer-line'>
                {imageSrc && (
                    <div className='AknTitleContainer-imageContainer AknImage--readOnly'>
                        <img className='AknImage-display' src={imageSrc} />
                    </div>
                )}

                {imageSrc === undefined && imageIllustration && (
                    <IllustrationContainer>
                        {cloneElement(imageIllustration, {width: 142, height: 142})}
                    </IllustrationContainer>
                )}
                <div className='AknTitleContainer-mainContainer'>
                    <div>
                        <div className='AknTitleContainer-line'>
                            <AknTitleContainerBreadcrumbs>{breadcrumb}</AknTitleContainerBreadcrumbs>
                            <div className='AknTitleContainer-buttonsContainer'>
                                {tag}
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
                        {contextContainer}
                    </div>
                </div>
            </div>
        </Header>
    </>
);
