import React from 'react';
import styled from '../../../common/styled-with-theme';
import {Documentation} from '../../model/ConnectionError';
import {DocumentationMessage} from './DocumentationMessage';

type Props = {
    documentations: Array<Documentation>;
};

export const DocumentationList = ({documentations}: Props) => {
    // TODO: change when 'DocumentationMessageType' will be available.
    const checkDocumentations = documentations
        .filter((documentation, i) => i < documentations.length)
        .map((documentation, i) => (
            <div key={i}>
                <DocumentationMessage documentation={documentation} />
            </div>
        ));

    // TODO: change when 'DocumentationMessageType' will be available.
    const moreInformationDocumentations = documentations
        .filter((documentation, i) => i >= documentations.length)
        .map((documentation, i) => (
            <div key={i}>
                <DocumentationMessage documentation={documentation} />
            </div>
        ));

    return (
        <>
            {checkDocumentations}
            <MoreInformationHelper>{moreInformationDocumentations}</MoreInformationHelper>
        </>
    );
};

const MoreInformationHelper = styled.div`
    padding-top: 10px;
`;
