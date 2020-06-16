import React from 'react';
import {Typography} from '../../../common';
import {InfoIcon} from '../../../common/icons';
import styled from '../../../common/styled-with-theme';
import {Documentation, HrefType, RouteType} from '../../model/ConnectionError';
import {RouteDocumentationMessageParameter} from './RouteDocumentationMessageParameter';

type Props = {
    documentation: Documentation;
};

export const DocumentationMessage = ({documentation}: Props) => {
    const constructedMessage = documentation.message.split(/({[^{}]+})/).map((messagePart: string, i) => {
        const isNeedle = new RegExp(/^{([^{}]+)}$/);
        // TODO: change when 'DocumentationMessageType' will be available.
        if (!isNeedle.test(messagePart)) {
            if (-1 !== messagePart.search('More information about')) {
                return (
                    <InfoMessage key={i}>
                        <InfoImg />
                        {messagePart}
                    </InfoMessage>
                );
            }

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

const InfoMessage = styled.span`
    color: ${({theme}) => theme.color.grey100};
`;

const InfoImg = styled(InfoIcon)`
    width: 16px;
    height: 16px;
    margin-right: 4px;
    vertical-align: middle;
`;
