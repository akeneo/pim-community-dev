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
  arrayUnique,
  ArrowDownIcon,
  Button,
  CloseIcon,
  Dropdown,
  IconButton,
  Modal,
  SectionTitle as UppercaseTitle,
  Table,
  Title,
  useBooleanState,
  uuid,
} from 'akeneo-design-system';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {Context} from 'akeneoassetmanager/domain/model/context';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import styled from 'styled-components';
import Spacer from 'akeneoassetmanager/application/component/app/spacer';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {getLabel} from 'pimui/js/i18n';

type Updater = {
  id: string;
  channel: ChannelReference;
  locale: LocaleReference;
  attribute: NormalizedAttribute;
  data: Data;
  action: 'set' | 'add';
};

type AddAttributeDropdownProps = {
  attributes: NormalizedAttribute[];
  locale: string;
  alreadyUsed: string[];
  onAdd: (attribute: NormalizedAttribute) => void;
};
const AddAttributeDropdown = ({attributes, locale, alreadyUsed, onAdd}: AddAttributeDropdownProps) => {
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <Dropdown>
      <Button level="tertiary" ghost onClick={open}>
        Add attribute <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Attributes</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {attributes
              .filter(attribute => !attribute.is_read_only)
              .map(attribute => {
                return (
                  <Dropdown.Item
                    key={attribute.identifier}
                    onClick={() => {
                      onAdd(attribute);
                      close();
                    }}
                  >
                    {getLabel(attribute.labels, locale, attribute.code)}{' '}
                    {alreadyUsed.includes(attribute.identifier) && 'already used'}
                  </Dropdown.Item>
                );
              })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

type UpdaterRowProps = {
  updater: Updater;
  locale: string;
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  readOnly: boolean;
  errors: ValidationError[];
};
const UpdaterRow = ({updater, locale, readOnly, errors, onRemove}: UpdaterRowProps) => {
  const translate = useTranslate();

  return (
    <Table.Row>
      <Table.Cell>{getLabel(updater.attribute.labels, locale, updater.attribute.code)}</Table.Cell>
      <Table.Cell>
        {JSON.stringify(updater.data)}
        {readOnly ? 'readonly' : 'editable'}
        {errors.map(error => JSON.stringify(error)).join(', ')}
      </Table.Cell>
      <Table.Cell>
        {updater.channel}
        {updater.locale}
        {updater.action}
      </Table.Cell>
      <Table.Cell>
        <IconButton
          level="tertiary"
          icon={<CloseIcon />}
          ghost="borderless"
          title={translate('pim_common.remove')}
          onClick={() => onRemove(updater)}
        />
      </Table.Cell>
    </Table.Row>
  );
};

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
        action: 'set',
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

const Container = styled.div`
  width: 100%;
  max-height: calc(100vh - 160px);
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow-y: auto;
`;
const SectionTitle = styled.div`
  display: flex;
`;
const EmptyUpdaterCollection = styled.span``;
const Header = styled.div`
  position: sticky;
  top: 80px;
`;
const Label = styled.span``;

type MassEditModalProps = {
  assetFamily: AssetFamily;
  context: Context;
  selectedAssetCount: number;
  onConfirm: () => void;
  onCancel: () => void;
};

const normalizeUpdater = (updater: Updater) => {
  return {
    channel: updater.channel,
    locale: updater.locale,
    attribute: updater.attribute.identifier,
    data: updater.data,
    action: updater.action,
  };
};
const normalizeUpdaterCollection = (updaterCollection: Updater[]) => {
  return updaterCollection.map(updater => normalizeUpdater(updater));
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
          <UppercaseTitle color="brand">{translate('pim_asset_manager.asset.mass_edit.title')}</UppercaseTitle>
          <Title>{translate('pim_common.confirm_edition')}</Title>
          {translate('pim_asset_manager.asset.mass_edit.extra_information')}
          <SectionTitle>
            <Label>{translate('Attributes')}</Label>
            <Spacer />
            {translate(
              'pim_asset_manager.asset.mass_edit.attribute_selected',
              {count: usedAttributeIdentifiers.length},
              usedAttributeIdentifiers.length
            )}
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
      </Container>
    </Modal>
  );
};

export {MassEditModal};
