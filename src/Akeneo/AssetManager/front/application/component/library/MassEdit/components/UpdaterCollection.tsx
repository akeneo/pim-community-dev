import React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {Table} from 'akeneo-design-system';
import {UpdaterRow} from 'akeneoassetmanager/application/component/library/MassEdit/components/UpdaterRow';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import Channel from 'akeneoassetmanager/domain/model/channel';

type UpdaterCollectionProps = {
  updaterCollection: Updater[];
  locale: string;
  readOnly: boolean;
  errors: ValidationError[];
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  channels: Channel[];
};

const UpdaterCollection = ({
  updaterCollection,
  locale,
  readOnly,
  errors,
  onRemove,
  onChange,
  channels,
}: UpdaterCollectionProps) => {
  return (
    <Table>
      <Table.Body>
        {updaterCollection.map(updater => (
          <UpdaterRow
            key={updater.id}
            updater={updater}
            readOnly={readOnly}
            errors={errors}
            onChange={onChange}
            onRemove={onRemove}
            uiLocale={locale}
            channels={channels}
          />
        ))}
      </Table.Body>
    </Table>
  );
};

export {UpdaterCollection};
