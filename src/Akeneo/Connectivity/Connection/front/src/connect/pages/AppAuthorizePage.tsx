import React, {FC} from 'react';
import styled from "styled-components";
import {useLocation} from "react-router-dom";
import {useTranslate} from "../../shared/translate";

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
`;

export const AppAuthorizePage: FC = () => {
    const translate = useTranslate();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const error = query.get('error');

    return (
        <FullScreen>
            {translate(error || '')}
        </FullScreen>
    );
};
