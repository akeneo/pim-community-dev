import React, {memo} from 'react';
import {Table} from './Table';
import {Button} from '../Button/Button';

const Row = memo(({row, ...rest}: any) => {
  return (
    <Table.Row key={`row-${row.id}`} {...rest}>
      <Table.Cell>{row.name}</Table.Cell>
      <Table.ActionCell>
        <Button level="primary" onClick={() => {}} ghost>
          Button
        </Button>
      </Table.ActionCell>
    </Table.Row>
  );
});

export {Row};
