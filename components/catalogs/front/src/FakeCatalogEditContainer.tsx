import React, {FC, PropsWithChildren, useRef, useState} from 'react';
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
    const [isDirty, setIsDirty] = useState<boolean>(false);

    const handleChange = (isDirty: boolean) => {
        setIsDirty(isDirty);
    }

    return (
        <>
            <SaveButton
                level='primary'
                onClick={() => {
                    catalogEditRef.current && catalogEditRef.current.save();
                }}
                disabled={!isDirty}
                className={'AknButton'}
            >
                Save
            </SaveButton>
            <CatalogEdit id={id} onChange={handleChange} ref={catalogEditRef} />
        </>
    );
};

export {FakeCatalogEditContainer};
