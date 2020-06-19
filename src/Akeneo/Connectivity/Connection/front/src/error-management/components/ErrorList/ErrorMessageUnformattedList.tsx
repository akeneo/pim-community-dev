import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent} from '../../model/ConnectionError';

type Props = {
    content: ConnectionErrorContent;
};

const hiddenFields = [
    'message',
    'product',
    'documentation',
    'message_template',
    'message_parameters',
    'locale',
    'scope',
];

const ErrorMessageUnformattedList: FC<Props> = ({content}) => {
    return (
        <ErrorTable>
            <tbody>
                {Object.entries(content)
                    .filter(([key]) => !hiddenFields.includes(key))
                    .map(([key, value], i) => {
                        return (
                            <ErrorTr key={i}>
                                <ErrorKey>{key}:</ErrorKey>
                                <td>{JSON.stringify(value)}</td>
                            </ErrorTr>
                        );
                    })}
            </tbody>
        </ErrorTable>
    );
};

const ErrorKey = styled.th`
    text-align: left;
    font-weight: bold;
    vertical-align: baseline;
    white-space: pre-wrap;
`;

const ErrorTr = styled.tr`
    line-height: 12px;
`;

const ErrorTable = styled.table`
    padding-top: 15px;
    border-collapse: separate;
`;

export {ErrorMessageUnformattedList};
