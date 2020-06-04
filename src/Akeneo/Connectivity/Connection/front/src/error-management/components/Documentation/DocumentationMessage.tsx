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
        const isNeedle = new RegExp(/^{([^{}]+)}$/);
        if (!isNeedle.test(messagePart)) {
            return <Message key={i}>{messagePart}</Message>;
        }
        const needle = isNeedle.exec(messagePart);
        if (
            null === needle ||
            2 !== needle.length ||
            !Object.prototype.hasOwnProperty.call(documentation.parameters, needle[1])
        ) {
            return <Message key={i}>{messagePart}</Message>;
        }

        const messageParameter = documentation.parameters[needle[1]];
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
