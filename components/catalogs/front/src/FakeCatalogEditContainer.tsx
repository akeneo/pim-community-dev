import React, {FC, PropsWithChildren, useRef} from 'react';
import {useParams} from 'react-router';
import {CatalogEdit, CatalogEditRef} from './components/CatalogEdit';
import {Button} from 'akeneo-design-system';
import styled from 'styled-components';

const SaveButton = styled(Button)`
    position: absolute;
    top: 40px;
    right: 40px;
`;

type Props = {};

const FakeCatalogEditContainer: FC<PropsWithChildren<Props>> = () => {
    const {id} = useParams<{id: string}>();
    const catalogEditRef = useRef<CatalogEditRef>(null);

    return (
        <>
            <SaveButton
                level="primary"
                onClick={() => {
                    catalogEditRef.current && catalogEditRef.current.save();
                }}
                className={'AknButton'}
            >
                Save
            </SaveButton>
            <CatalogEdit id={id} ref={catalogEditRef} />
        </>
    );
};

export {FakeCatalogEditContainer};
