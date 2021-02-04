/**
 * Features:
 * - Steps (edit, confirm)
 * - Validation (asking for back to validate)
 * - Attribute/locale/channel/action selection
 * - Attribute/locale/channel/action removable
 * - Update model
 *
 * Workflow:
 * - Add attribute
 *   - change context if needed (channel and locales cannot be empty if value per locale or value per channel)
 *   - change the action type if needed (same here)
 *   - change value (or leave it blank)
 *   - remove (or not) a line
 *   - add (or not) a line
 * - Next step
 * - Validation
 * - Fix error if needed
 * - Confirm step (everything is read only)
 * - Close modal
 * - Notification
 *
 * Requirements:
 * - Grid context (for default locale/channel)
 * - Family (for attribute list)
 * - List of locales and channel
 *
 * Questions:
 * - Are we sure error will be properly mapped to frontend collection
 */

import React, {useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {
  Button,
  getColor,
  Modal,
  SectionTitle as UppercaseTitle,
  Title
} from 'akeneo-design-system';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import Spacer from 'akeneoassetmanager/application/component/app/spacer';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import Separator from '../../app/separator';
import {useUpdaterCollection} from './useUpdaterCollection';
import {AddAttributeDropdown} from './components/AttributeDropdown';
import {UpdaterCollection} from './components/UpdaterCollection';
import {normalizeUpdaterCollection, Updater} from './model/updater';

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

const Progress = styled.div`
  height: 80px; //TODO: to validate
`;

const SectionTitle = styled.div`
  display: flex;
  width: 100%;
  align-items: center;
  border-bottom: 1px solid ${getColor('grey', 140)};
`;

const EmptyUpdaterCollection = styled.span``;

const Header = styled.div`
  width: 100%;
  align-items: center;
  display: flex;
  flex-direction: column;
`;

type MassEditModalProps = {
  assetFamily: AssetFamily;
  context: Context;
  selectedAssetCount: number;
  onConfirm: () => void;
  onCancel: () => void;
};

const massEditLauncher = {
  validate: async (
    _assetFamilyIdentifier: AssetFamilyIdentifier,
    updaterCollection: Updater[]
  ): Promise<ValidationError[]> => {
    const _normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    return Promise.resolve([]);
  },
  launch: async (
    _assetFamilyIdentifier: AssetFamilyIdentifier,
    _query: Query,
    updaterCollection: Updater[]
  ): Promise<void> => {
    const _normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    return Promise.resolve();
  },
};

const MassEditModal = ({assetFamily, context, selectedAssetCount, onCancel, onConfirm}: MassEditModalProps) => {
  const translate = useTranslate();
  const [updaterCollection, addUpdater, removeUpdater, setUpdater, usedAttributeIdentifiers] = useUpdaterCollection();
  const [step, setStep] = useState<'edit' | 'confirm'>('edit');
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleMoveToConfirmStep = async () => {
    setErrors([]);
    const errors = await massEditLauncher.validate(assetFamily.identifier, updaterCollection);
    if (errors.length) {
      setErrors(errors);

      return;
    }

    setStep('confirm');
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel}>
      <Modal.TopRightButtons>
        {'edit' === step && (
          <>
            <Button level="tertiary" onClick={onCancel}>
              {translate('pim_common.cancel')}
            </Button>
            <Button onClick={handleMoveToConfirmStep}>{translate('pim_common.next')}</Button>
          </>
        )}
        {'confirm' === step && (
          <>
            <Button level="tertiary" onClick={() => setStep('edit')}>
              {translate('pim_common.previous')}
            </Button>
            <Button onClick={onConfirm}>{translate('pim_common.confirm')}</Button>
          </>
        )}
      </Modal.TopRightButtons>
      <Container>
        <Header>
          <UppercaseTitle color="brand">{translate('pim_asset_manager.asset.mass_edit.subtitle')}</UppercaseTitle>
          <Title>{translate('pim_asset_manager.asset.mass_edit.title', {count: selectedAssetCount}, selectedAssetCount)}</Title>
          {translate('pim_asset_manager.asset.mass_edit.extra_information')}
          <SectionTitle>
            <UppercaseTitle>{translate('Attributes')}</UppercaseTitle>
            <Spacer />
            {translate(
              'pim_asset_manager.asset.mass_edit.attribute_selected',
              {count: usedAttributeIdentifiers.length},
              usedAttributeIdentifiers.length
            )}
            <Separator />
            <AddAttributeDropdown
              onAdd={attribute => {
                addUpdater(attribute, context);
              }}
              locale={context.locale}
              attributes={assetFamily.attributes}
              alreadyUsed={usedAttributeIdentifiers}
            />
          </SectionTitle>
        </Header>
        <Content>
          {0 === updaterCollection.length ? (
            <EmptyUpdaterCollection>Is empty</EmptyUpdaterCollection>
          ) : (
            <UpdaterCollection
              updaterCollection={updaterCollection}
              locale={context.locale}
              readOnly={step == 'confirm'}
              errors={errors}
              onRemove={updater => removeUpdater(updater.id)}
              onChange={updater => setUpdater(updater)}
            />
          )}
        </Content>
        <Progress>{step}</Progress>
      </Container>
    </Modal>
  );
};

export {MassEditModal};
