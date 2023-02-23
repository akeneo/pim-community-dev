import React, {FC, MutableRefObject, PropsWithChildren, useLayoutEffect, useRef, useState} from 'react';
import {useParams} from 'react-router';
import {CatalogEdit, useCatalogForm} from './components/CatalogEdit';
import {Button} from 'akeneo-design-system';
import styled from 'styled-components';
import {NotificationLevel, useDependenciesContext} from '@akeneo-pim-community/shared';

const TopRightContainer = styled.div`
    position: absolute;
    top: 40px;
    right: 40px;
    display: flex;
    flex-direction: column;
    align-items: end;
`;
const CatalogStatusWidgetContainer = styled.div`
    position: absolute;
    top: 100px;
    left: 260px;
`;

const DirtyWarning = styled.div`
    font-style: italic;
    color: #11324d;
    border-bottom: 1px solid #f8b441;
    margin: 8px 0 0;
`;

type Props = {};

const FakeCatalogEditContainer: FC<PropsWithChildren<Props>> = () => {
    const {id} = useParams<{id: string}>();
    const [form, save, isDirty] = useCatalogForm(id);
    const {notify} = useDependenciesContext();
    const ref = useRef<HTMLDivElement>() as MutableRefObject<HTMLDivElement>;
    const [headerContextContainer, setHeaderContextContainer] = useState<HTMLDivElement | undefined>(undefined);
    useLayoutEffect(() => {
        setHeaderContextContainer(ref.current);
    }, [ref]);

    const saveHandler = async () => {
        const isSaveSuccessful = await save();

        if (notify) {
            if (isSaveSuccessful) {
                notify(NotificationLevel.SUCCESS, 'Catalog is saved');
            } else {
                notify(NotificationLevel.ERROR, 'Catalog have errors');
            }
        }
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
            </TopRightContainer>
            <CatalogStatusWidgetContainer ref={ref} />
            <CatalogEdit id={id} form={form} headerContextContainer={headerContextContainer} />
        </>
    );
};

export {FakeCatalogEditContainer};
