import React, {useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, getColor, Modal, ProgressIndicator, SectionTitle, useProgress} from 'akeneo-design-system';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {useUpdaterCollection} from './hooks/useUpdaterCollection';
import {AddAttributeDropdown} from './components/AddAttributeDropdown';
import {EmptyUpdaterCollection} from './components/EmptyUpdaterCollection';
import {UpdaterCollection} from './components/UpdaterCollection';
import Channel from 'akeneoassetmanager/domain/model/channel';
import massEditLauncher from 'akeneoassetmanager/infrastructure/mass-edit-launcher';
import {Updater} from './model/updater';

const Container = styled.div`
  width: 100%;
  max-height: 100vh;
  padding-top: 40px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const Content = styled.div`
  flex-grow: 1;
  overflow-y: auto;
  width: 100%;
`;

const Header = styled.div`
  width: 100%;
  align-items: center;
  display: flex;
  flex-direction: column;
`;

const Footer = styled.div`
  width: 100%;
  background-color: ${getColor('white')};
  height: 80px;
`;

type MassEditModalProps = {
  assetFamily: AssetFamily;
  context: Context;
  selectedAssetCount: number;
  onConfirm: (updaterCollection: Updater[]) => void;
  onCancel: () => void;
  channels: Channel[];
};

const MassEditModal = ({
  assetFamily,
  context,
  selectedAssetCount,
  onCancel,
  onConfirm,
  channels,
}: MassEditModalProps) => {
  const translate = useTranslate();
  const [updaterCollection, addUpdater, removeUpdater, setUpdater, usedAttributeIdentifiers] = useUpdaterCollection();
  const steps = ['edit', 'confirm'];
  const [isCurrentStep, nextStep, previousStep] = useProgress(steps);
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleConfirm = () => {
    onConfirm(updaterCollection);
  };

  const handleClose = () => {
    if (updaterCollection.length > 0 && !confirm(translate('pim_ui.flash.unsaved_changes'))) {
      return;
    }

    onCancel();
  };

  const handleMoveToConfirmStep = async () => {
    setErrors([]);
    const errors = await massEditLauncher.validate(assetFamily.identifier, updaterCollection);
    if (errors.length) {
      setErrors(errors);

      return;
    }

    nextStep();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={handleClose}>
      <Modal.TopRightButtons>
        {isCurrentStep('edit') && (
          <>
            <Button level="tertiary" onClick={onCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button onClick={handleMoveToConfirmStep}>{translate('pim_common.next')}</Button>
          </>
        )}
        {isCurrentStep('confirm') && (
          <>
            <Button level="tertiary" onClick={previousStep}>
              {translate('pim_common.previous')}
            </Button>
            <Button onClick={handleConfirm}>{translate('pim_common.confirm')}</Button>
          </>
        )}
      </Modal.TopRightButtons>
      <Container>
        <Header>
          <Modal.SectionTitle color="brand">
            {translate('pim_asset_manager.asset.mass_edit.subtitle')}
          </Modal.SectionTitle>
          <Modal.Title>
            {translate('pim_asset_manager.asset.mass_edit.title', {count: selectedAssetCount}, selectedAssetCount)}
          </Modal.Title>
          {translate('pim_asset_manager.asset.mass_edit.extra_information')}
          <SectionTitle>
            <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
            <SectionTitle.Spacer />
            <SectionTitle.Information>
              {translate(
                'pim_asset_manager.asset.mass_edit.attribute_selected',
                {count: usedAttributeIdentifiers.length},
                usedAttributeIdentifiers.length
              )}
            </SectionTitle.Information>
            {isCurrentStep('edit') && (
              <>
                <SectionTitle.Separator />
                <AddAttributeDropdown
                  onAdd={attribute => {
                    addUpdater(attribute, context);
                  }}
                  uiLocale={context.locale}
                  attributes={assetFamily.attributes}
                  alreadyUsed={usedAttributeIdentifiers}
                />
              </>
            )}
          </SectionTitle>
        </Header>
        <Content>
          {0 === updaterCollection.length ? (
            <EmptyUpdaterCollection />
          ) : (
            <UpdaterCollection
              updaterCollection={updaterCollection}
              locale={context.locale}
              readOnly={isCurrentStep('confirm')}
              errors={errors}
              onRemove={updater => removeUpdater(updater.id)}
              onChange={updater => setUpdater(updater)}
              channels={channels}
            />
          )}
        </Content>
        <Footer>
          <ProgressIndicator>
            {steps.map(step => (
              <ProgressIndicator.Step key={step} current={isCurrentStep(step)}>
                {translate(`pim_common.${step}`)}
              </ProgressIndicator.Step>
            ))}
          </ProgressIndicator>
        </Footer>
      </Container>
    </Modal>
  );
};

export {MassEditModal};
