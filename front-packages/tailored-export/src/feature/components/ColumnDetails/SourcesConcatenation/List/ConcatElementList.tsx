import React from 'react';
import {Table} from 'akeneo-design-system';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';
import {ColumnConfiguration, ConcatElement} from '../../../../models';
import {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow} from './SourceRow';
import {StringRow} from './StringRow';

type ConcatElementListProps = {
  validationErrors: ValidationError[];
  columnConfiguration: ColumnConfiguration;
  onConcatElementReorder: (newIndices: number[]) => void;
  onConcatElementChange: (updatedConcatElement: ConcatElement) => void;
  onConcatElementRemove: (elementUuid: string) => void;
};

const ConcatElementList = ({
  validationErrors,
  columnConfiguration,
  onConcatElementReorder,
  onConcatElementChange,
  onConcatElementRemove,
}: ConcatElementListProps) => {
  return (
    <Table isDragAndDroppable={true} onReorder={onConcatElementReorder}>
      <Table.Body>
        {columnConfiguration.format.elements.map(element => {
          if ('string' === element.type) {
            return (
              <StringRow
                key={element.uuid}
                validationErrors={filterErrors(validationErrors, `[${element.uuid}]`)}
                concatElement={element}
                onConcatElementChange={onConcatElementChange}
                onConcatElementRemove={onConcatElementRemove}
              />
            );
          }

          const source = columnConfiguration.sources.find(({uuid}) => uuid === element.uuid);

          switch (source?.type) {
            case 'attribute':
              return <AttributeSourceRow source={source} key={element.uuid} />;
            case 'property':
              return <PropertySourceRow source={source} key={element.uuid} />;
            case 'association_type':
              return <AssociationTypeSourceRow source={source} key={element.uuid} />;
            default:
              throw new Error(`Source with uuid ${element.value} not found`);
          }
        })}
      </Table.Body>
    </Table>
  );
};

export {ConcatElementList};
