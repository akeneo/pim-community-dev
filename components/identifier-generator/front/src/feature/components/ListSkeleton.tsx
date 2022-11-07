import React from 'react';
import {SkeletonPlaceholder, Table} from 'akeneo-design-system';

const ListSkeleton: React.FC = () => (
  <>
    {[...Array(3)].map((_, i) => (
      <Table.Row key={i}>
        <Table.Cell>
          <SkeletonPlaceholder>This is a loading label</SkeletonPlaceholder>
        </Table.Cell>
        <Table.Cell>
          <SkeletonPlaceholder>Loading identifier</SkeletonPlaceholder>
        </Table.Cell>
        <Table.ActionCell>
          <SkeletonPlaceholder>Loading buttons</SkeletonPlaceholder>
        </Table.ActionCell>
      </Table.Row>
    ))}
  </>
);

export {ListSkeleton};
