import React, {FC} from 'react';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent} from '../../model/ConnectionError';

type Props = {
    content: ConnectionErrorContent;
};

const hiddenFields = ['product', 'documentation', 'message_template', 'message_parameters', 'locale', 'scope'];

const ErrorMessageUnformattedList: FC<Props> = ({content}) => {
    return (
        <table>
            <tbody>
                {Object.entries(content)
                    .filter(([key]) => !hiddenFields.includes(key))
                    .map(([key, value], i) => {
                        return (
                            <tr key={i}>
                                <ErrorKey>{key}:</ErrorKey>
                                <td>{JSON.stringify(value)}</td>
                            </tr>
                        );
                    })}
            </tbody>
        </table>
    );
};

const ErrorKey = styled.th`
    text-align: left;
    font-weight: bold;
    vertical-align: baseline;
    white-space: pre-wrap;
`;

export {ErrorMessageUnformattedList};
