import React, {FC, useState} from 'react';
import styled from 'styled-components';
import {getColor, SectionTitle, SwitcherButton, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourcePlaceholder} from './components/SourcePlaceholder';
import {useCatalog} from './hooks/useCatalog';
import {TargetPlaceholder} from './components/TargetPlaceholder';
import {useProductMappingSchema} from './hooks/useProductMappingSchema';

const MappingContainer = styled.div`
    display: flex;
    gap: 40px;
    padding-top: 10px;
`;
const TargetContainer = styled.div`
    flex-basis: 50%;
    flex-grow: 1;
`;
const SourceContainer = styled.div`
    flex-basis: 50%;
`;
const TargetCell = styled(Table.Cell)`
    width: 215px;
    color: ${getColor('brand', 100)};
    font-style: italic;
`;
const PlaceholderCell = styled(Table.Cell)`
    color: ${getColor('grey', 100)};
    font-style: italic;
`;

type Props = {
    catalogId: string;
};

export const ProductMapping: FC<Props> = ({catalogId}) => {
    const translate = useTranslate();
    const {data: catalog} = useCatalog(catalogId);
    const {data: productMappingSchema} = useProductMappingSchema(catalogId);

    const [selectedTarget, setSelectedTarget] = useState<string>();

    const targets = Object.entries(catalog?.product_mapping ?? {});

    return (
        <MappingContainer data-testid={'product-mapping'}>
            <TargetContainer>
                <SectionTitle>
                    <SectionTitle.Title>{translate('akeneo_catalogs.product_mapping.target.title')}</SectionTitle.Title>
                    <SectionTitle.Spacer />
                    <SwitcherButton label={translate('akeneo_catalogs.product_mapping.target.filter.label')}>
                        {translate('akeneo_catalogs.product_mapping.target.filter.option.all')}
                    </SwitcherButton>
                </SectionTitle>
                <Table>
                    <Table.Header>
                        <Table.HeaderCell>
                            {translate('akeneo_catalogs.product_mapping.target.table.target')}
                        </Table.HeaderCell>
                        <Table.HeaderCell>
                            {translate('akeneo_catalogs.product_mapping.target.table.source')}
                        </Table.HeaderCell>
                    </Table.Header>
                    <Table.Body>
                        {(targets.length === 0 || undefined === productMappingSchema) && <TargetPlaceholder />}
                        {targets.length > 0 && undefined !== productMappingSchema && (
                            <>
                                <Table.Row>
                                    <TargetCell>UUID</TargetCell>
                                    <Table.Cell>UUID</Table.Cell>
                                </Table.Row>
                                {targets.map(([target, source]) => {
                                    if ('uuid' === target) {
                                        return;
                                    }

                                    return (
                                        <Table.Row
                                            key={target}
                                            onClick={() => {
                                                setSelectedTarget(target);
                                            }}
                                        >
                                            <TargetCell>
                                                {productMappingSchema.properties[target]?.title ?? target}
                                            </TargetCell>
                                            {null === source.source && (
                                                <PlaceholderCell>
                                                    {translate('akeneo_catalogs.product_mapping.target.table.placeholder')}
                                                </PlaceholderCell>
                                            )}
                                            {source.source && <Table.Cell>{source.source}</Table.Cell>}
                                        </Table.Row>
                                    );
                                })}
                            </>
                        )}
                    </Table.Body>
                </Table>
            </TargetContainer>
            <SourceContainer>
                {selectedTarget === undefined && <SourcePlaceholder />}
                {selectedTarget && (
                    <SectionTitle>
                        <SectionTitle.Title>{selectedTarget}</SectionTitle.Title>
                    </SectionTitle>
                )}
            </SourceContainer>
        </MappingContainer>
    );
};
