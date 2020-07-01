import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {messageWithColoredParameters} from '../../message-with-colored-parameters';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {ErrorMessageUnformattedList} from './ErrorMessageUnformattedList';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageDomain: FC<Props> = ({content}) => {
    const unformattedList =
        undefined === content.documentation ? <ErrorMessageUnformattedList content={content} /> : <></>;
    return (
        <>
            {'string' === typeof content?.message_template && undefined !== content?.message_parameters ? (
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

export {ErrorMessageDomain};
