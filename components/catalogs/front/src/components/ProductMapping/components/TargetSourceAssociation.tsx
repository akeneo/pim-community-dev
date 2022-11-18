import React, {FC, memo} from 'react';
import styled from 'styled-components';
import {getColor, Pill, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourceLabel} from './SourceLabel';
import {Source} from '../models/Source';

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

type Props = {
    isSelected: boolean;
    targetCode: string;
    targetLabel: string | undefined;
    source: Source | null;
    onClick: (targetCode: string, source: Source|null) => void;
    hasError: boolean;
};

export const TargetSourceAssociation: FC<Props> = memo(({isSelected, targetCode, targetLabel, source, onClick, hasError}) => {
    const translate = useTranslate();

    return (
        <Table.Row key={targetCode} onClick={() => onClick(targetCode, source)} isSelected={isSelected} >
            <TargetCell>{targetLabel ?? targetCode}</TargetCell>
            {(null === source || null === source.source) && (
                <PlaceholderCell>
                    {translate('akeneo_catalogs.product_mapping.target.table.placeholder')}
                </PlaceholderCell>
            )}
            {null !== source && source.source && (
                <Table.Cell>
                    <SourceLabel sourceCode={source.source} />
                    {hasError && <ErrorPill data-testid='error-pill' level='danger' />}
                </Table.Cell>
            )}
        </Table.Row>
    );
});
