import React, {useMemo, useState} from 'react';
import {CreateGeneratorModal} from '../components/CreateGeneratorModal';
import {IdentifierGenerator} from '../../models';
import {CreateGenerator} from '../components/CreateGenerator';
import {Breadcrumb, Button} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {Common} from './Common';

enum Screen {
  LIST,
  CREATE_MODAL,
  CREATE_PAGE,
}

const List: React.FC<{}> = () => {
  const translate = useTranslate();
  const [currentScreen, setCurrentScreen] = useState<Screen>(Screen.LIST);
  const [identifierGenerator, setIdentifierGenerator] = useState<IdentifierGenerator>();
  const generators = []; // TMP

  const openModal = () => setCurrentScreen(Screen.CREATE_MODAL);
  const closeModal = () => setCurrentScreen(Screen.LIST);
  const openCreatePage = (identifierGenerator: IdentifierGenerator) => {
    setCurrentScreen(Screen.CREATE_PAGE);
    setIdentifierGenerator(identifierGenerator);
  };

  const creationIsDisabled = useMemo(() => generators.length > 0, [generators.length]);

  return (
    <>
      {currentScreen === Screen.LIST && (
        <>
          <Common.Helper />
          <PageHeader>
            <PageHeader.Breadcrumb>
              <Breadcrumb>
                <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
                <Breadcrumb.Step href="#">{translate('pim_title.akeneo_identifier_generator_index')}</Breadcrumb.Step>
              </Breadcrumb>
            </PageHeader.Breadcrumb>
            <PageHeader.UserActions>
              <PimView
                className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                viewName="pim-identifier-generator-user-navigation"
              />
            </PageHeader.UserActions>
            <PageHeader.Actions>
              <Button onClick={openModal} disabled={creationIsDisabled}>
                {translate('pim_common.create')}
              </Button>
            </PageHeader.Actions>
            <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
          </PageHeader>
        </>
      )}
      {currentScreen === Screen.CREATE_MODAL && <CreateGeneratorModal onClose={closeModal} onSave={openCreatePage} />}
      {currentScreen === Screen.CREATE_PAGE && identifierGenerator && (
        <CreateGenerator initialGenerator={identifierGenerator} />
      )}
    </>
  );
};

export {List};
