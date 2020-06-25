import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {messageWithColoredParameters} from '../../message-with-colored-parameters';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {ErrorMessageUnformattedList} from './ErrorMessageUnformattedList';

const isParsableTemplate = (template: string, parameters: {[param: string]: string}) => {
    if (undefined === parameters || 'string' !== typeof Object.keys(parameters)[0]) {
        return false;
    }

    return -1 !== template.search(Object.keys(parameters)[0]);
};

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageViolation: FC<Props> = ({content}) => {
    const unformattedList =
        undefined === content.documentation ? <ErrorMessageUnformattedList content={content} /> : <></>;

    return (
        <>
            {undefined !== content?.message_template &&
            undefined !== content?.message_parameters &&
            isParsableTemplate(content?.message_template, content.message_parameters) ? (
                <ErrorMessage>
                    {messageWithColoredParameters(content.message_template, content.message_parameters, content.type)}
                </ErrorMessage>
            ) : (
                <>
                    <ErrorMessage>{content.message}</ErrorMessage>
                    {unformattedList}
                </>
            )}
        </>
    );
};

const ErrorMessage = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
    font-weight: bold;
`;

export {ErrorMessageViolation};
