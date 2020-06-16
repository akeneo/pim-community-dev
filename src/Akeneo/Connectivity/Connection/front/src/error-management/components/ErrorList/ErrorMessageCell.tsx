import React, {FC} from 'react';
import TableCell from '../../../common/components/Table/TableCell';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {DocumentationList} from '../Documentation/DocumentationList';
import {ErrorProductInformation} from './ErrorProductInformation';

const messageWithColoredParameters = (template: string, parameters: {[param: string]: string}) => {
    const messageComponents = Object.entries(parameters).reduce(
        (messageComponents, [key, value]) => {
            return messageComponents
                .map((messageComponent, i) => {
                    if (typeof messageComponent !== 'string') {
                        return messageComponent;
                    }

                    const messageParts = messageComponent.split('{' + key + '}');

                    if (1 === messageParts.length) {
                        return messageParts;
                    }

                    messageParts.splice(1, 0, <ColoredParameters key={i}>{value}</ColoredParameters>);

                    return messageParts;
                })
                .flat();
        },
        [template]
    );
    return messageComponents;
};

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageCell: FC<Props> = ({content}) => {
    return (
        <Container>
            {content?.product && <ErrorProductInformation product={content.product} />}
            {'domain_error' === content?.type &&
            'string' === typeof content?.message_template &&
            null !== content?.message_parameters ? (
                <ErrorContentRow>
                    {messageWithColoredParameters(content?.message_template, content.message_parameters)}
                </ErrorContentRow>
            ) : (
                <table>
                    <tbody>
                        {Object.entries(content)
                            .filter(
                                ([key]) =>
                                    'product' !== key &&
                                    'documentation' !== key &&
                                    'message_template' !== key &&
                                    'message_parameters' !== key &&
                                    'locale' !== key &&
                                    'scope' !== key
                            )
                            .map(([key, value], i) => {
                                return (
                                    <ErrorContentRow key={i}>
                                        <ErrorContentKeyCell>{key}</ErrorContentKeyCell>
                                        <td>: {JSON.stringify(value)}</td>
                                    </ErrorContentRow>
                                );
                            })}
                    </tbody>
                </table>
            )}
            {content.documentation !== undefined && <DocumentationList documentations={content.documentation} />}
        </Container>
    );
};

const Container = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
`;

const ErrorContentRow = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
`;

const ColoredParameters = styled.span`
    color: ${({theme}) => theme.color.red100};
`;

const ErrorContentKeyCell = styled.th`
    text-align: left;
    font-weight: normal;
    white-space: pre-wrap;
`;

export {ErrorMessageCell};
