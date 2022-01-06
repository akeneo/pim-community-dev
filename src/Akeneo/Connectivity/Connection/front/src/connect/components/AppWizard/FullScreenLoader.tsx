import React, {FC} from 'react';
import {useTranslate} from '../../../shared/translate';
import styled from 'styled-components';
import loaderImage from '../../../common/assets/illustrations/main-loader.gif';

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
`;
const Loader = styled.div`
    width: 940px;
    font-size: 28px;
    display: block;
    margin: 200px auto 0;
    text-align: center;
    line-height: 40px;
`;

export const FullScreenLoader: FC = () => {
    const translate = useTranslate();

    return (
        <FullScreen>
            <Loader>
                <h3>{translate('akeneo_connectivity.connection.connect.apps.loader.message')}</h3>
                <img src={loaderImage} />
            </Loader>
        </FullScreen>
    );
};
