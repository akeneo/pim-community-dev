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
    'type',
];

const ErrorMessageUnformattedList: FC<Props> = ({content}) => {
    if (Object.entries(content).filter(([key]) => !hiddenFields.includes(key)).length === 0) {
        return <></>;
    }

    return (
        <ErrorTable>
            <tbody>
                {Object.entries(content)
                    .filter(([key]) => !hiddenFields.includes(key))
                    .map(([key, value], i) => {
                        return (
                            <tr key={i}>
                                <ErrorKey>{key}:</ErrorKey>
                                <ErrorValue>{JSON.stringify(value)}</ErrorValue>
                            </tr>
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
    padding: 0px;
`;

const ErrorValue = styled.td`
    padding: 0px;
`;

const ErrorTable = styled.table`
    padding: 10px 0 0 0;
    border-collapse: separate;
    border-spacing: 0px 4px;
`;

export {ErrorMessageUnformattedList};
