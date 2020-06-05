import styled from '../../../common/styled-with-theme';
import React, {FC, useContext} from 'react';
import {RouteParameter} from '../../model/ConnectionError';
import {RouterContext} from '../../../shared/router';

type Props = {
    routeParam: RouteParameter;
};

export const RouteDocumentationMessageParameter: FC<Props> = ({routeParam}) => {
    const router = useContext(RouterContext);

    return (
        <InternalLink onClick={() => router.redirect(router.generate(routeParam.route, routeParam.routeParameters))}>
            {routeParam.title}
        </InternalLink>
    );
};

const InternalLink = styled.span`
    color: ${({theme}) => theme.color.purple100};
    font-size: ${({theme}) => theme.fontSize.default};
    text-decoration: underline;
    cursor: pointer;
    :hover {
        text-decoration: none;
    }
`;
