import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {ErrorMessageUnformattedList} from './ErrorMessageUnformattedList';
import {messageWithColoredParameters} from './ErrorMessageUtil';

const isParsableTemplate = (template: string, parameters: {[param: string]: string} | undefined) => {
    if (undefined === parameters || 'string' !== typeof Object.keys(parameters)[0]) {
        return false;
    }

    return -1 !== template.search(Object.keys(parameters)[0]);
};

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageViolation: FC<Props> = ({content}) => {
    return (
        <>
            {'string' === typeof content?.message_template &&
            null !== content?.message_parameters &&
            isParsableTemplate(content?.message_template, content.message_parameters) ? (
                <ErrorMessage>
                    {messageWithColoredParameters(content.message_template, content.message_parameters, content.type)}
                </ErrorMessage>
            ) : (
                <ErrorMessageUnformattedList content={content} />
            )}
        </>
    );
};

const ErrorMessage = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
    font-weight: bold;
`;

export {ErrorMessageViolation};
