import React, {useState} from 'react';
import styled from 'styled-components';
import {
  Button,
  getColor,
  Helper,
  Link,
  Modal,
  ProgressIndicator,
  SectionTitle,
  useProgress,
} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {useUpdaterCollection} from 'akeneoassetmanager/application/component/library/MassEdit/hooks/useUpdaterCollection';
import {AddAttributeDropdown} from 'akeneoassetmanager/application/component/library/MassEdit/components/AddAttributeDropdown';
import {EmptyUpdaterCollection} from 'akeneoassetmanager/application/component/library/MassEdit/components/EmptyUpdaterCollection';
import {UpdaterCollection} from 'akeneoassetmanager/application/component/library/MassEdit/components/UpdaterCollection';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import {useMassEdit} from 'akeneoassetmanager/application/component/library/MassEdit/hooks/useMassEdit';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {getErrorsForPath} from '@akeneo-pim-community/shared';

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
  selectionQuery: Query;
  selectedAssetCount: number;
  onConfirm: (updaterCollection: Updater[]) => void;
  onCancel: () => void;
  channels: Channel[];
};

const MassEditModal = ({
  assetFamily,
  selectionQuery,
  context,
  selectedAssetCount,
  onCancel,
  onConfirm,
  channels,
}: MassEditModalProps) => {
  const translate = useTranslate();
  const [validateMassEdit] = useMassEdit();
  const [updaterCollection, addUpdater, removeUpdater, setUpdater, usedAttributeIdentifiers] = useUpdaterCollection();
  const steps = ['edit', 'confirm'];
  const [isCurrentStep, nextStep, previousStep] = useProgress(steps);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const notify = useNotify();

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
    try {
      const errors = await validateMassEdit(assetFamily.identifier, selectionQuery, updaterCollection);
      if (errors.length) {
        const [globalError] = getErrorsForPath(errors, '');
        if (globalError) {
          notify(NotificationLevel.ERROR, translate(globalError.messageTemplate));
        }

        setErrors(errors);

        return;
      }

      nextStep();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_asset_manager.asset.notification.mass_edit.validation.fail'));
    }
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={handleClose}>
      <Modal.TopRightButtons>
        {isCurrentStep('edit') && (
          <>
            <Button level="tertiary" onClick={onCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button onClick={handleMoveToConfirmStep} disabled={0 === updaterCollection.length}>
              {translate('pim_common.next')}
            </Button>
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
          <Helper>
            {translate('pim_asset_manager.asset.mass_edit.helper.content')}&nbsp;
            <Link
              target="_blank"
              href="https://help.akeneo.com/pim/serenity/articles/work-on-your-assets.html#bulk-edit-multiple-assets"
            >
              {translate('pim_asset_manager.asset.mass_edit.helper.link')}
            </Link>
          </Helper>
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
