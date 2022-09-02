import React, {MutableRefObject} from 'react';
import {SelectOptionWithId} from './ManageOptionsModal';
import {AkeneoThemedProps, CloseIcon, Helper, IconButton, Key, Table, TextInput, getColor} from 'akeneo-design-system';
import styled, {css} from 'styled-components';
import {LocaleCode, useTranslate} from '@akeneo-pim-community/shared';

const ManageOptionsRowContainer = styled(Table.Row)<{isSticky: boolean} & AkeneoThemedProps>`
  ${({isSticky}) =>
    isSticky
      ? css`
          position: sticky;
          bottom: -1px;
          background-color: ${getColor('white')};
          z-index: 1;

          & > td {
            border-bottom: 1px solid ${getColor('white')};
          }
        `
      : ''}
`;

const ManageOptionCell = styled(Table.Cell)`
  vertical-align: top;
`;

const CellFieldContainer = styled.div`
  flex-grow: 1;
`;

type ManageOptionsRowProps = {
  option: SelectOptionWithId;
  onChange: (option: SelectOptionWithId) => void;
  onDelete?: () => void;
  violations?: string[];
  isSelected?: boolean;
  onSelect: () => void;
  labelPlaceholder?: string;
  codeInputRef?: MutableRefObject<any>;
  labelInputRef?: MutableRefObject<any>;
  localeCode: LocaleCode;
  forceAutocomplete?: boolean;
  onCodeEnter?: () => void;
  onLabelEnter?: () => void;
  isSticky?: boolean;
};

const ManageOptionsRow: React.FC<ManageOptionsRowProps> = ({
  option,
  onChange,
  onDelete,
  onSelect,
  violations = [],
  isSelected = false,
  labelPlaceholder,
  codeInputRef,
  labelInputRef,
  localeCode,
  forceAutocomplete = false,
  onCodeEnter,
  onLabelEnter,
  isSticky = false,
  ...rest
}) => {
  const translate = useTranslate();
  const [autocompleteMode, setAutocompleteMode] = React.useState<boolean>(option.isNew || forceAutocomplete);

  const formatCode = (label: string) => label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 100);
  const handleLabelChange = (label: string) => {
    option.labels[localeCode] = label;
    if (autocompleteMode || forceAutocomplete) {
      option.code = formatCode(label);
    }
    onChange(option);
  };

  const handleCodeChange = (code: string) => {
    option.code = code;
    onChange(option);
  };

  const handleCodeFocus = () => {
    onSelect();
    setAutocompleteMode(false);
  };

  return (
    <ManageOptionsRowContainer isSelected={isSelected} onClick={onSelect} isSticky={isSticky} {...rest}>
      <ManageOptionCell>
        <CellFieldContainer>
          <TextInput
            ref={labelInputRef}
            value={option.labels[localeCode] || ''}
            onChange={handleLabelChange}
            maxLength={255}
            placeholder={labelPlaceholder}
            onFocus={onSelect}
            onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) {
                onLabelEnter?.();
              }
            }}
          />
        </CellFieldContainer>
      </ManageOptionCell>
      <ManageOptionCell>
        <CellFieldContainer>
          {option.isNew && (
            <TextInput
              ref={codeInputRef}
              value={option.code || ''}
              onChange={handleCodeChange}
              maxLength={100}
              onFocus={handleCodeFocus}
              onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
                if (Key.Enter === event.key) {
                  onCodeEnter?.();
                }
              }}
            />
          )}
          {!option.isNew && <TextInput defaultValue={option.code || ''} disabled={true} readOnly={true} />}
          {violations.map((violation, i) => (
            <Helper key={i} level='error' inline>
              {violation}
            </Helper>
          ))}
        </CellFieldContainer>
      </ManageOptionCell>
      <Table.ActionCell>
        {onDelete && (
          <IconButton
            ghost='borderless'
            level='tertiary'
            icon={<CloseIcon />}
            title={translate('pim_common.remove')}
            onClick={onDelete}
            onFocus={onSelect}
          />
        )}
      </Table.ActionCell>
    </ManageOptionsRowContainer>
  );
};

export {ManageOptionsRow};
