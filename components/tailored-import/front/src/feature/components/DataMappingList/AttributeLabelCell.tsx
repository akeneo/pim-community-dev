import React from 'react';
import styled from 'styled-components';
import {SkeletonPlaceholder, Table} from 'akeneo-design-system';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {useAttribute} from '../../hooks';

const AttributeLabel = styled(Table.Cell)`
  width: 50px;
`;

const DisplayedLabel = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

type AttributeLabelCellProps = {
  attributeCode: string;
};

const AttributeLabelCell = ({attributeCode}: AttributeLabelCellProps) => {
  const [isFetching, attribute] = useAttribute(attributeCode);
  const catalogLocale = useUserContext().get('catalogLocale');
  const attributeLabel = getLabel(attribute?.labels ?? {}, catalogLocale, attributeCode);

  return (
    <AttributeLabel rowTitle={true}>
      {isFetching ? (
        <SkeletonPlaceholder>{attributeLabel}</SkeletonPlaceholder>
      ) : (
        <DisplayedLabel title={attributeLabel}>{attributeLabel}</DisplayedLabel>
      )}
    </AttributeLabel>
  );
};

export {AttributeLabelCell};
