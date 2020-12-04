import React from 'react';
import {Typography} from '../../../common';
import styled from '../../../common/styled-with-theme';
import {Documentation, DocumentationStyleInformation, HrefType, RouteType} from '../../model/ConnectionError';
import {RouteDocumentationMessageParameter} from './RouteDocumentationMessageParameter';
import {InfoRoundIcon, getColor} from 'akeneo-design-system';

type Props = {
    documentation: Documentation;
};

export const DocumentationMessage = ({documentation}: Props) => {
    const constructedMessage = documentation.message.split(/({[^{}]+})/).map((messagePart: string, i) => {
        const isNeedle = new RegExp(/^{([^{}]+)}$/);
        if (!isNeedle.test(messagePart)) {
            return <span key={i}>{messagePart}</span>;
        }

        const needle = isNeedle.exec(messagePart);
        if (
            null === needle ||
            2 !== needle.length ||
            !Object.prototype.hasOwnProperty.call(documentation.parameters, needle[1])
        ) {
            return <span key={i}>{messagePart}</span>;
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
                <>
                    <InfoImg />
                    {constructedMessage}
                </>
            ) : (
                constructedMessage
            )}
        </>
    );
};

const DocLink = styled(Typography.Link)`
    color: ${getColor('blue', 100)};
`;

const InfoImg = styled(InfoRoundIcon)`
    width: 16px;
    height: 16px;
    margin-right: 4px;
    vertical-align: middle;
    color: ${getColor('blue', 100)};
`;
