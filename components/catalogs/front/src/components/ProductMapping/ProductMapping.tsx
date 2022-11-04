import React, {FC, useState} from 'react';
import styled from 'styled-components';
import {SectionTitle, SwitcherButton, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourcePlaceholder} from './components/SourcePlaceholder';
import {useTargetsQuery} from './hooks/useTargetsQuery';
import {Target} from './models/Target';
import {TargetPlaceholder} from './components/TargetPlaceholder';

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
    color: #9452ba;
    font-style: italic;
    font-weight: 400;
    font-size: 13px;
`;

const PlaceholderCell = styled(Table.Cell)`
    color: #a1a9b7;
    font-style: italic;
    font-weight: 400;
    font-size: 13px;
`;

type Props = {
    id: string;
};

export const ProductMapping: FC<Props> = ({id}) => {
    const translate = useTranslate();

    const {data: targets} = useTargetsQuery(id);

    const [selectedTarget, setSelectedTarget] = useState<Target>();

    return (
        <MappingContainer>
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
                        {(undefined === targets || 0 === targets.length) && <TargetPlaceholder />}
                        {undefined !== targets &&
                            targets.map(target => {
                                return (
                                    <Table.Row
                                        key={target.code}
                                        onClick={() => {
                                            setSelectedTarget(target);
                                        }}
                                    >
                                        <TargetCell>{target.label}</TargetCell>
                                        <PlaceholderCell>
                                            {translate('akeneo_catalogs.product_mapping.target.table.placeholder')}
                                        </PlaceholderCell>
                                    </Table.Row>
                                );
                            })}
                    </Table.Body>
                </Table>
            </TargetContainer>
            <SourceContainer>
                {selectedTarget === undefined && <SourcePlaceholder />}
                {selectedTarget && (
                    <SectionTitle>
                        <SectionTitle.Title>{selectedTarget.label}</SectionTitle.Title>
                    </SectionTitle>
                )}
            </SourceContainer>
        </MappingContainer>
    );
};
