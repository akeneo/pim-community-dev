import React, {FC, PropsWithChildren, useState} from 'react';
import {useParams} from 'react-router';
import {CatalogEdit, useCatalogForm} from './components/CatalogEdit';
import {Button} from 'akeneo-design-system';
import styled from 'styled-components';

const TopRightContainer = styled.div`
    position: absolute;
    top: 40px;
    right: 40px;
    display: flex;
    flex-direction: column;
    align-items: end;
`;

const DirtyWarning = styled.div`
    font-style: italic;
    color: #11324d;
    border-bottom: 1px solid #f8b441;
    margin: 8px 0 0;
`;

const SuccessMessage = styled.div`
    font-style: italic;
    color: #67b373;
    border-bottom: 1px solid #3d6b45;
    margin: 8px 0 0;
`;

const ErrorsMessage = styled.div`
    font-style: italic;
    color: #d4604f;
    border-bottom: 1px solid #7f392f;
    margin: 8px 0 0;
`;

type Props = {};

const FakeCatalogEditContainer: FC<PropsWithChildren<Props>> = () => {
    const {id} = useParams<{id: string}>();
    const [form, save, isDirty] = useCatalogForm(id);
    const [isSuccess, setSuccess] = useState<boolean | null>(null);

    const saveHandler = async () => {
        const isSaveSuccessful = await save();
        setSuccess(isSaveSuccessful);
    };

    if (undefined === form) {
        return null;
    }

    return (
        <>
            <TopRightContainer>
                <Button level='primary' onClick={saveHandler} disabled={!isDirty} className={'AknButton'}>
                    Save
                </Button>
                {isDirty && <DirtyWarning>⚠️ There are unsaved changes.</DirtyWarning>}
                {isSuccess && <SuccessMessage>Catalog is saved</SuccessMessage>}
                {isSuccess === false && <ErrorsMessage>Catalog have errors</ErrorsMessage>}
            </TopRightContainer>
            <CatalogEdit form={form} />
        </>
    );
};

export {FakeCatalogEditContainer};
