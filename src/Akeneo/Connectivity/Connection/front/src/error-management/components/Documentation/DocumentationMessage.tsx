import React from 'react';
import {Typography} from '../../../common';
import {InfoIcon} from '../../../common/icons';
import styled from '../../../common/styled-with-theme';
import {Documentation, DocumentationStyleInformation, HrefType, RouteType} from '../../model/ConnectionError';
import {RouteDocumentationMessageParameter} from './RouteDocumentationMessageParameter';

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
                    <DocLink key={i} href={messageParameter.href} target='_blank'>
                        {messageParameter.title}
                    </DocLink>
                );
            case RouteType:
                return <RouteDocumentationMessageParameter key={i} routeParam={messageParameter} />;
        }
    });

    return (
        <>
            {DocumentationStyleInformation === documentation.style ? (
                <Message>
                    <InfoImg />
                    {constructedMessage}
                </Message>
            ) : (
                <Message>{constructedMessage}</Message>
            )}
        </>
    );
};

const Message = styled.span``;

const DocLink = styled(Typography.Link)`
    color: ${({theme}) => theme.color.blue100};
`;

const InfoImg = styled(InfoIcon)`
    width: 16px;
    height: 16px;
    margin-right: 4px;
    vertical-align: middle;
`;
