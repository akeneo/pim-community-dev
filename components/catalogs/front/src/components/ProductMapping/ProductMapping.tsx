import React, {FC, useCallback, useState} from 'react';
import styled from 'styled-components';
import {getColor, SectionTitle, SwitcherButton, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourcePlaceholder} from './components/SourcePlaceholder';
import {TargetPlaceholder} from './components/TargetPlaceholder';
import {ProductMapping as ProductMappingType} from './models/ProductMapping';
import {ProductMappingSchema} from './models/ProductMappingSchema';
import {TargetSourceAssociation} from './components/TargetSourceAssociation';
import {SourceLabel} from './components/SourceLabel';

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

type Props = {
    productMapping: ProductMappingType;
    productMappingSchema?: ProductMappingSchema;
};

export const ProductMapping: FC<Props> = ({productMapping, productMappingSchema}) => {
    const translate = useTranslate();

    const [selectedTarget, setSelectedTarget] = useState<string>();

    const handleClick = useCallback(targetCode => {
        setSelectedTarget(targetCode);
    }, []);

    const targets = Object.entries(productMapping ?? {});

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
                                {targets.map(([targetCode, source]) => {
                                    if ('uuid' === targetCode) {
                                        return;
                                    }

                                    return (
                                        <TargetSourceAssociation
                                            key={targetCode}
                                            onClick={handleClick}
                                            targetCode={targetCode}
                                            targetLabel={productMappingSchema.properties[targetCode]?.title}
                                            sourceCode={source.source}
                                        />
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
                        <SectionTitle.Title>
                            <SourceLabel sourceCode={selectedTarget} />
                        </SectionTitle.Title>
                    </SectionTitle>
                )}
            </SourceContainer>
        </MappingContainer>
    );
};
