import {useState} from 'react';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {arrayUnique, uuid} from 'akeneo-design-system';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';

const useUpdaterCollection = () => {
  const [updaterCollection, setUpdaterCollection] = useState<Updater[]>([]);

  const addUpdater = (attribute: NormalizedAttribute, context: Context) => {
    setUpdaterCollection(updaterCollection => [
      ...updaterCollection,
      {
        id: uuid(),
        channel: attribute.value_per_channel ? context.channel : null,
        locale: attribute.value_per_locale ? context.locale : null,
        attribute: attribute,
        data: null,
        action: 'replace',
      },
    ]);
  };

  const removeUpdater = (idToDelete: string) => {
    setUpdaterCollection(updaterCollection => updaterCollection.filter(updater => updater.id !== idToDelete));
  };

  const setUpdater = (updaterToSet: Updater) => {
    setUpdaterCollection(updaterCollection =>
      updaterCollection.map(updater => (updater.id === updaterToSet.id ? updaterToSet : updater))
    );
  };

  return [
    updaterCollection,
    addUpdater,
    removeUpdater,
    setUpdater,
    arrayUnique(updaterCollection.map(updater => updater.attribute.identifier)),
  ] as const;
};

export {useUpdaterCollection};
