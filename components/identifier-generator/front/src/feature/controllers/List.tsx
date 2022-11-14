import React, {useState} from 'react';
import {CreateGeneratorModal, CreateGeneratorPage} from '../pages/';
import {IdentifierGenerator} from '../models';
import {ListPage} from '../pages/ListPage';

enum Screen {
  LIST,
  CREATE_MODAL,
  CREATE_PAGE,
}

const List: React.FC = () => {
  const [currentScreen, setCurrentScreen] = useState<Screen>(Screen.LIST);
  const [identifierGenerator, setIdentifierGenerator] = useState<IdentifierGenerator>();

  const openModal = () => setCurrentScreen(Screen.CREATE_MODAL);
  const closeModal = () => setCurrentScreen(Screen.LIST);
  const openCreatePage = (identifierGenerator: IdentifierGenerator) => {
    setCurrentScreen(Screen.CREATE_PAGE);
    setIdentifierGenerator(identifierGenerator);
  };

  return (
    <>
      {currentScreen === Screen.LIST && <ListPage onCreate={openModal} />}
      {currentScreen === Screen.CREATE_MODAL && <CreateGeneratorModal onClose={closeModal} onSave={openCreatePage} />}
      {currentScreen === Screen.CREATE_PAGE && identifierGenerator && (
        <CreateGeneratorPage initialGenerator={identifierGenerator} />
      )}
    </>
  );
};

export {List};
