import React from 'react';
import {Documentation} from '../../model/ConnectionError';
import {DocumentationMessage} from './DocumentationMessage';

type Props = {
    documentations: Array<Documentation>;
};

export const DocumentationList = ({documentations}: Props) => {
    const list = documentations.map((documentation, i) => (
        <div key={i}>
            <DocumentationMessage documentation={documentation} />
        </div>
    ));

    return <>{list}</>;
};
