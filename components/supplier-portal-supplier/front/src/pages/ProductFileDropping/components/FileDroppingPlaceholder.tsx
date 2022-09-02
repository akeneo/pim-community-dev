import {FormattedMessage} from 'react-intl';
import React from 'react';
import styled from 'styled-components';

const FileDroppingPlaceholder = () => {
    return (
        <div>
            <FormattedMessage
                defaultMessage="<b>Drag & drop</b> your file <fileExtension>(xlsx)</fileExtension> to launch the upload"
                id="I/h32J"
                values={{
                    b: chunks => <b>{chunks}</b>,
                    fileExtension: chunks => <FileExtension>{chunks}</FileExtension>,
                }}
            />
            <PlaceholderUploadAlternative>
                - <FormattedMessage defaultMessage="or" id="Ntjkqd" /> -
            </PlaceholderUploadAlternative>
        </div>
    );
};

const PlaceholderUploadAlternative = styled.div`
    text-align: center;
    color: #a1a9b7;
`;
const FileExtension = styled.span`
    color: #a1a9b7;
`;

export {FileDroppingPlaceholder};
