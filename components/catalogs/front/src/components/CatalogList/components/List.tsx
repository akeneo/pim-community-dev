import React, {FC, PropsWithChildren} from 'react';
import {Table} from 'akeneo-design-system';
import {useCatalogs} from '../hooks/useCatalogs';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Empty} from './Empty';
import {Status} from './Status';

type Props = {
    owner: string;
    onCatalogClick: (catalogId: string) => void;
};

const List: FC<PropsWithChildren<Props>> = ({owner, onCatalogClick}) => {
    const translate = useTranslate();

    const catalogs = useCatalogs(owner);

    if (catalogs.isLoading) {
        return null;
    }

    if (catalogs.isError || undefined === catalogs.data) {
        throw new Error(catalogs.error?.message || undefined);
    }

    return (
        <>
            <Table>
                <Table.Header>
                    <Table.HeaderCell>{translate('akeneo_catalogs.catalog_list.catalogs_name')}</Table.HeaderCell>
                    <Table.HeaderCell>{translate('akeneo_catalogs.catalog_list.status')}</Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                    {catalogs.data.map(catalog => (
                        <Table.Row key={catalog.id} onClick={() => onCatalogClick(catalog.id)}>
                            <Table.Cell>{catalog.name}</Table.Cell>
                            <Table.Cell>
                                <Status enabled={catalog.enabled} />
                            </Table.Cell>
                        </Table.Row>
                    ))}
                </Table.Body>
            </Table>
            {0 === catalogs.data.length && <Empty />}
        </>
    );
};

export {List};
