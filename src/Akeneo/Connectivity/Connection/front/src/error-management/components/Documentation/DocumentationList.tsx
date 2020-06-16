import React from 'react';
import styled from '../../../common/styled-with-theme';
import {Documentation, DocumentationStyleInformation, DocumentationStyleText} from '../../model/ConnectionError';
import {DocumentationMessage} from './DocumentationMessage';

type Props = {
    documentations: Array<Documentation>;
};

export const DocumentationList = ({documentations}: Props) => {
    const checkDocumentations = documentations
        .filter(documentation => documentation.style === DocumentationStyleText)
        .map((documentation, i) => (
            <div key={i}>
                <DocumentationMessage documentation={documentation} />
            </div>
        ));

    const moreInformationDocumentations = documentations
        .filter(documentation => documentation.style === DocumentationStyleInformation)
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
    color: ${({theme}) => theme.color.grey100};
`;
