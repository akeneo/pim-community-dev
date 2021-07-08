import React, {forwardRef, SyntheticEvent, useMemo} from 'react';
import styled from 'styled-components';
import {CloseIcon, getColor, Helper, IconButton, Pill, Table, TextInput, useBooleanState} from 'akeneo-design-system';
import {DeleteModal, getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ColumnConfiguration} from '../../models/ColumnConfiguration';
import {useValidationErrors} from '../../contexts';
import {useAssociationTypes, useAttributes} from '../../hooks';

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const SourceList = styled.div`
  margin-left: 20px;
  text-overflow: ellipsis;
  overflow: hidden;
`;

const SourceListPlaceholder = styled.span`
  color: ${getColor('grey', 100)};
  font-style: italic;
  display: flex;
  align-items: center;
  width: 100%;
`;

const TargetCell = styled(Table.Cell)`
  width: 400px;
`;

const RemoveCell = styled(Table.Cell)`
  width: 50px;
  padding-left: 0;
`;

const Spacer = styled.div`
  flex: 1;
`;

type ColumnRowProps = {
  column: ColumnConfiguration;
  isSelected: boolean;
  onColumnChange: (column: ColumnConfiguration) => void;
  onColumnSelected: (uuid: string | null) => void;
  onColumnRemoved: (uuid: string) => void;
  onFocusNext: (uuid: string) => void;
};

const ColumnRow = forwardRef<HTMLInputElement, ColumnRowProps>(
  (
    {column, isSelected, onColumnChange, onFocusNext, onColumnSelected, onColumnRemoved, ...rest}: ColumnRowProps,
    ref
  ) => {
    const translate = useTranslate();
    const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();

    const handleColumnRemove = (event: SyntheticEvent) => {
      event.stopPropagation();
      openDeleteModal();
    };

    const handleConfirmColumnRemove = () => {
      onColumnRemoved(column.uuid);
      onFocusNext(column.uuid);
    };

    const targetErrors = useValidationErrors(`[columns][${column.uuid}][target]`, true);
    const hasError = useValidationErrors(`[columns][${column.uuid}]`).length > 0 && 0 === targetErrors.length;
    const userContext = useUserContext();
    const catalogLocale = userContext.get('catalogLocale');
    const attributeCodes = useMemo(
      () => column.sources.filter(({type}) => 'attribute' === type).map(({code}) => code),
      [column.sources]
    );

    const associationTypeCodes = useMemo(
      () => column.sources.filter(({type}) => 'association' === type).map(({code}) => code),
      [column.sources]
    );

    const attributes = useAttributes(attributeCodes);
    const associationTypes = useAssociationTypes(associationTypeCodes);

    return (
      <>
        <Table.Row key={column.uuid} onClick={() => onColumnSelected(column.uuid)} isSelected={isSelected} {...rest}>
          <TargetCell>
            <Field>
              <TextInput
                ref={ref}
                onChange={updatedValue => onColumnChange({...column, target: updatedValue})}
                onSubmit={() => onFocusNext(column.uuid)}
                placeholder={translate('akeneo.tailored_export.column_list.column_row.target_placeholder')}
                invalid={targetErrors.length !== 0}
                value={column.target}
              />
              {targetErrors.map((error, index) => (
                <Helper key={index} inline={true} level="error">
                  {translate(error.messageTemplate, error.parameters)}
                </Helper>
              ))}
            </Field>
          </TargetCell>
          <Table.Cell>
            <SourceList>
              {0 === column.sources.length ? (
                <SourceListPlaceholder>
                  {translate('akeneo.tailored_export.column_list.column_row.no_source')}
                </SourceListPlaceholder>
              ) : (
                column.sources
                  .map(source =>
                    'attribute' === source.type
                      ? getLabel(
                        attributes.find(attribute => attribute.code === source.code)?.labels ?? {},
                        catalogLocale,
                        source.code
                      )
                      : 'association' === source.type ?
                        getLabel(
                          associationTypes.find(associationType => associationType.code === source.code)?.labels ?? {},
                          catalogLocale,
                          source.code
                        )
                      : translate(`pim_common.${source.code}`)
                  )
                  .join(', ')
              )}
            </SourceList>
            <Spacer />
            {hasError && <Pill level="danger" />}
          </Table.Cell>
          <RemoveCell>
            <IconButton
              ghost="borderless"
              level="tertiary"
              icon={<CloseIcon />}
              title={translate('akeneo.tailored_export.column_list.column_row.remove')}
              onClick={handleColumnRemove}
            />
          </RemoveCell>
        </Table.Row>
        {isDeleteModalOpen && (
          <DeleteModal
            title={translate('akeneo.tailored_export.column_list.title')}
            confirmButtonLabel={translate('pim_common.confirm')}
            onConfirm={handleConfirmColumnRemove}
            onCancel={closeDeleteModal}
          >
            {translate('akeneo.tailored_export.column.delete_message')}
          </DeleteModal>
        )}
      </>
    );
  }
);

export {ColumnRow, TargetCell};
