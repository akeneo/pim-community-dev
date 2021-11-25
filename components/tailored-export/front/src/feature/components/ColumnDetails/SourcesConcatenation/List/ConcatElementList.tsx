import React from 'react';
import {Table} from 'akeneo-design-system';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';
import {ConcatElement, Format, Source} from '../../../../models';
import {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow} from './SourceRow';
import {TextRow} from './TextRow';

type ConcatElementListProps = {
  validationErrors: ValidationError[];
  sources: Source[];
  format: Format;
  onConcatElementReorder: (newIndices: number[]) => void;
  onConcatElementChange: (updatedConcatElement: ConcatElement) => void;
  onConcatElementRemove: (elementUuid: string) => void;
};

const ConcatElementList = ({
  validationErrors,
  sources,
  format,
  onConcatElementReorder,
  onConcatElementChange,
  onConcatElementRemove,
}: ConcatElementListProps) => {
  return (
    <Table isDragAndDroppable={true} onReorder={onConcatElementReorder}>
      <Table.Body>
        {format.elements.map(element => {
          if ('text' === element.type) {
            return (
              <TextRow
                key={element.uuid}
                validationErrors={filterErrors(validationErrors, `[${element.uuid}]`)}
                concatElement={element}
                onConcatElementChange={onConcatElementChange}
                onConcatElementRemove={onConcatElementRemove}
              />
            );
          }

          const source = sources.find(({uuid}) => uuid === element.uuid);

          if (undefined === source) {
            throw new Error(`Source with uuid ${element.value} not found`);
          }

          switch (source.type) {
            case 'attribute':
              return <AttributeSourceRow source={source} key={element.uuid} />;
            case 'property':
              return <PropertySourceRow source={source} key={element.uuid} />;
            case 'association_type':
              return <AssociationTypeSourceRow source={source} key={element.uuid} />;
            default:
              throw new Error('Invalid source type');
          }
        })}
      </Table.Body>
    </Table>
  );
};

export {ConcatElementList};
