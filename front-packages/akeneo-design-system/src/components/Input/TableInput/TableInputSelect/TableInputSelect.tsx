import React, {ReactNode} from 'react';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {useBooleanState} from '../../../../hooks';
import {ArrowDownIcon, CloseIcon, LockIcon} from '../../../../icons';
import {Search} from '../../../Search/Search';
import styled, {css} from 'styled-components';
import {IconButton} from '../../../IconButton/IconButton';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const SelectButtonDropdown = styled(Dropdown)<{readOnly: boolean} & AkeneoThemedProps>`
  width: 100%;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
`;

const SelectButton = styled.button<{highlighted: boolean; inError: boolean; readOnly: boolean} & AkeneoThemedProps>`
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  display: inline-block;
  justify-content: space-between;
  padding: 0 ${({readOnly}) => (readOnly ? '35px' : '70px')} 0 10px;
  height: 39px;
  line-height: 39px;
  align-items: center;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  ${({highlighted, inError}) =>
    highlighted && !inError
      ? css`
          background: ${getColor('green', 10)};
          box-shadow: 0 0 0 1px ${getColor('green', 80)};
        `
      : css`
          background: none;
        `};

  ${({inError}) =>
    inError
      ? css`
          background: ${getColor('red', 10)};
          box-shadow: 0 0 0 1px ${getColor('red', 80)};
        `
      : css`
          background: none;
        `};
`;

const IconsPart = styled.div`
  display: inline-flex;
  gap: 10px;
  position: absolute;
  right: 10px;
  height: 39px;
  align-items: center;
`;

type TableInputSelectProps = {
  value: ReactNode | null;
  onClear: () => void;
  highlighted?: boolean;
  clearLabel: string;
  openDropdownLabel: string;
  onNextPage?: () => void;
  searchValue?: string;
  onSearchChange?: (search: string) => void;
  searchPlaceholder: string;
  searchTitle: string;
  inError?: boolean;
  readOnly?: boolean;
};

const TableInputSelect: React.FC<TableInputSelectProps> = ({
  value,
  onClear,
  clearLabel,
  openDropdownLabel,
  highlighted = false,
  searchValue = '',
  searchPlaceholder,
  onSearchChange,
  searchTitle,
  onNextPage,
  children,
  inError,
  readOnly = false,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);

  React.useEffect(() => {
    close();
    handleSearchChange('');
  }, [value]);

  const handleSearchChange = (search: string) => {
    if (onSearchChange) onSearchChange(search);
  };

  return (
    <SelectButtonDropdown readOnly={readOnly} {...rest}>
      <SelectButton
        readOnly={readOnly}
        onClick={() => {
          if (!readOnly) open();
        }}
        tabIndex={-1}
        highlighted={highlighted}
        title={value}
        inError={inError}
      >
        {value}&nbsp;
      </SelectButton>
      <IconsPart>
        {value && !readOnly && !isOpen && (
          <IconButton
            icon={<CloseIcon />}
            size="small"
            title={clearLabel}
            ghost="borderless"
            level="tertiary"
            onClick={onClear}
          />
        )}
        {!readOnly && (
          <IconButton
            icon={<ArrowDownIcon />}
            size="small"
            title={openDropdownLabel}
            ghost="borderless"
            level="tertiary"
            onClick={open}
          />
        )}
        {readOnly && <LockIcon size={16} />}
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
          <Dropdown.Header>
            <Search
              onSearchChange={handleSearchChange}
              placeholder={searchPlaceholder}
              searchValue={searchValue}
              title={searchTitle}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection onNextPage={onNextPage}>{children}</Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </SelectButtonDropdown>
  );
};

export {TableInputSelect};
