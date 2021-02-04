import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import {Table} from 'akeneo-design-system';
import React from 'react';
import {UpdaterRow} from './UpdaterRow';
import {Updater} from '../model/updater';

type UpdaterCollectionProps = {
  updaterCollection: Updater[];
  locale: string;
  readOnly: boolean;
  errors: ValidationError[];
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
};

const UpdaterCollection = ({
  updaterCollection,
  locale,
  readOnly,
  errors,
  onRemove,
  onChange,
}: UpdaterCollectionProps) => {
  return (
    <Table>
      <Table.Body>
        {updaterCollection.map((updater, _index) => (
          <UpdaterRow
            key={updater.id}
            updater={updater}
            locale={locale}
            readOnly={readOnly}
            errors={errors}
            onChange={onChange}
            onRemove={onRemove}
          />
        ))}
      </Table.Body>
    </Table>
  );
};

export {UpdaterCollection};
