import {Documentation, HrefType, RouteType} from '../../model/ConnectionError';
import {Typography} from '../../../common';
import React from 'react';
import {RouteDocumentationMessageParameter} from './RouteDocumentationMessageParameter';
import styled from '../../../common/styled-with-theme';

type Props = {
    documentation: Documentation;
};

export const DocumentationMessage = ({documentation}: Props) => {
    const constructedMessage = documentation.message.split(/({[^{}]+})/).map((messagePart: string, i) => {
        const isNeedle = new RegExp(/^{[^{}]+}$/);
        if (
            !isNeedle.test(messagePart) ||
            !Object.prototype.hasOwnProperty.call(documentation.parameters, messagePart)
        ) {
            return <Message key={i}>{messagePart}</Message>;
        }
        const messageParameter = documentation.parameters[messagePart];
        switch (messageParameter.type) {
            case HrefType:
                return (
                    <Typography.Link key={i} href={messageParameter.href} target='_blank'>
                        {messageParameter.title}
                    </Typography.Link>
                );
            case RouteType:
                return <RouteDocumentationMessageParameter key={i} routeParam={messageParameter} />;
        }
    });

    return <>{constructedMessage}</>;
};
const Message = styled.span`
    color: ${({theme}) => theme.color.grey140};
`;
