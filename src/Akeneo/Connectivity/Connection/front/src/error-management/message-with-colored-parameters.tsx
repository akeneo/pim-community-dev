import React, {ReactElement} from 'react';
import styled from '../common/styled-with-theme';
import {ErrorMessageDomainType} from './model/ConnectionError';

const messageWithColoredParameters = (template: string, parameters: {[param: string]: string}, type: string) => {
    let componentIndex = 0;
    const messageComponents = Object.entries(parameters).reduce(
        (messageComponents, [key, value]) => {
            return messageComponents
                .map(messageComponent => {
                    if (typeof messageComponent !== 'string') {
                        return messageComponent;
                    }

                    const messageParts: Array<string | ReactElement> = messageComponent.split(
                        ErrorMessageDomainType === type ? '{' + key + '}' : key
                    );

                    if (1 === messageParts.length) {
                        return messageParts;
                    }

                    messageParts.splice(1, 0, <ColoredParameters key={componentIndex}>{value}</ColoredParameters>);
                    componentIndex++;

                    return messageParts;
                })
                .flat();
        },
        [template] as Array<string | ReactElement>
    );
    return messageComponents;
};

const ColoredParameters = styled.span`
    color: ${({theme}) => theme.color.red100};
`;

export {messageWithColoredParameters};
