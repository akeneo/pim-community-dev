import React from 'react';
import styled from 'styled-components';
import {getColor, Table} from 'akeneo-design-system';
import {ColumnConfiguration} from '../../../models';
import {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow} from './SourceRow';
import {StringRow} from './StringRow';

const ConcatenationListContainer = styled.span`
  color: ${getColor('grey', 140)};
`;

type ConcatenationListProps = {
  columnConfiguration: ColumnConfiguration;
  onColumnConfigurationChange: (columnConfiguration: ColumnConfiguration) => void;
};

const ConcatenationList = ({columnConfiguration}: ConcatenationListProps) => {
  return (
    <ConcatenationListContainer>
      <Table isDragAndDroppable={true} onReorder={() => {}}>
        <Table.Body>
          {columnConfiguration.format.elements.map((element, index) => {
            if ('string' === element.type) {
              return <StringRow key={index} value={element.value} onChange={() => {}} onRemove={() => {}} />;
            }

            const source = columnConfiguration.sources.find(source => source.uuid === element.value);

            switch (source?.type) {
              case 'attribute':
                return <AttributeSourceRow source={source} key={index} />;
              case 'property':
                return <PropertySourceRow source={source} key={index} />;
              case 'association_type':
                return <AssociationTypeSourceRow source={source} key={index} />;
              default:
                throw new Error(`Source with uuid ${element.value} not found`);
            }
          })}
        </Table.Body>
      </Table>
    </ConcatenationListContainer>
  );
};

export {ConcatenationList};
