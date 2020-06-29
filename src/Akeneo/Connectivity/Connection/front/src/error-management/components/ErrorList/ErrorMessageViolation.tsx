import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {messageWithColoredParameters} from '../../message-with-colored-parameters';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {ErrorMessageUnformattedList} from './ErrorMessageUnformattedList';

/**
 * Checks if a message template contains at least one given parameter.
 * This function is useful for the moment because sometimes,
 * "content.message_template" contains a translation key
 * (like 'pim_catalog.constraint.invalid_variant_product_parent')
 * instead of the real parsable message.
 * @todo Remove this function when message_template has no longer a translation key.
 * @param template the message template
 * @param parameters the list of parameters
 */
const messageTemplateHasParameter = (template: string, parameters: {[param: string]: string}) => {
    if (undefined === parameters || 'string' !== typeof Object.keys(parameters)[0]) {
        return false;
    }

    let hasParameters = false;
    Object.keys(parameters).map(key => {
        if (-1 !== template.search(key)) {
            hasParameters = true;
        }
    });

    return hasParameters;
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
            messageTemplateHasParameter(content?.message_template, content.message_parameters) ? (
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
