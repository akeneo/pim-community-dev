import {Documentation, HrefType, RouteType} from '../../model/ConnectionError';
import {Typography} from '../../../common';
import React from 'react';
import {RouteDocumentationMessageParameter} from './RouteDocumentationMessageParameter';
import styled from '../../../common/styled-with-theme';

type Props = {
    documentation: Documentation;
};

export const DocumentationMessage = ({documentation}: Props) => {
    const constructedMessage = documentation.message.split(/(%s)/).map((messagePart, i) => {
        if (messagePart !== '%s') {
            return <Message key={i}>{messagePart}</Message>;
        }
        const param = documentation.params.shift();
        if (undefined === param) {
            return;
        }

        switch (param.type) {
            case HrefType:
                return (
                    <Typography.Link key={i} href={param.href} target='_blank'>
                        {param.title}
                    </Typography.Link>
                );
            case RouteType:
                return <RouteDocumentationMessageParameter key={i} routeParam={param} />;
        }
    });

    return <>{constructedMessage}</>;
};
const Message = styled.span`
    color: ${({theme}) => theme.color.grey140};
`;
