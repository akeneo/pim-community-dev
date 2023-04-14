import React, {FC, memo} from 'react';
import styled from 'styled-components';
import {getColor, Pill, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourceLabel} from './SourceLabel';
import {Source} from '../../models/Source';
import {SourceDefaultValue} from './SourceDefaultValue';

const TargetCell = styled(Table.Cell)`
    width: 215px;
    color: ${getColor('brand', 100)};
    font-style: italic;
`;
const PlaceholderCell = styled(Table.Cell)`
    color: ${getColor('grey', 100)};
    font-style: italic;
`;
const ErrorPill = styled(Pill)`
    margin-left: 10px;
`;
const RequiredPill = styled(Pill)`
    margin-left: 10px;
`;

type Props = {
    isSelected: boolean;
    targetCode: string;
    targetLabel: string | undefined;
    source: Source;
    onClick: (targetCode: string, source: Source) => void;
    hasError: boolean;
    isRequired: boolean;
};

export const TargetSourceAssociation: FC<Props> = memo(
    ({isSelected, targetCode, targetLabel, source, onClick, hasError, isRequired}) => {
        const translate = useTranslate();

        return (
            <Table.Row key={targetCode} onClick={() => onClick(targetCode, source)} isSelected={isSelected}>
                <TargetCell>
                    {targetLabel ?? targetCode}
                    {isRequired && <RequiredPill level='warning' data-testid='required-pill' />}
                </TargetCell>
                {null === source.source && undefined === source.default && (
                    <PlaceholderCell>
                        {translate('akeneo_catalogs.product_mapping.target.table.placeholder')}
                        {hasError && <ErrorPill data-testid='error-pill' level='danger' />}
                    </PlaceholderCell>
                )}
                {'uuid' === targetCode && <Table.Cell>UUID</Table.Cell>}
                {null !== source.source && 'uuid' !== targetCode && (
                    <Table.Cell>
                        <SourceLabel sourceCode={source.source} />
                        {hasError && <ErrorPill data-testid='error-pill' level='danger' />}
                    </Table.Cell>
                )}
                {null === source.source && undefined !== source.default && (
                    <Table.Cell>
                        <SourceDefaultValue sourceDefaultValue={source.default} />
                        {hasError && <ErrorPill data-testid='error-pill' level='danger' />}
                    </Table.Cell>
                )}
            </Table.Row>
        );
    }
);
