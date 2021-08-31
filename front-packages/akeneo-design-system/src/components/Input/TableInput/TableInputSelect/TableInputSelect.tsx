import React, {ReactNode} from 'react';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {useBooleanState} from '../../../../hooks';
import {ArrowDownIcon, CloseIcon} from '../../../../icons';
import {Search} from '../../../Search/Search';
import styled, {css} from 'styled-components';
import {IconButton} from '../../../IconButton/IconButton';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputReadOnlyCell} from '../TableInputReadOnlyCell';

const SelectButtonDropdown = styled(Dropdown)`
  width: 100%;
  color: ${getColor('grey', 140)};
`;

const SelectButton = styled.button<{highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  display: inline-block;
  justify-content: space-between;
  padding: 0 70px 0 10px;
  height: 39px;
  line-height: 39px;
  align-items: center;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: none;

  ${({highlighted, inError}) =>
    highlighted &&
    !inError &&
    css`
      background: ${getColor('green', 10)};
      box-shadow: 0 0 0 1px ${getColor('green', 80)};
    `};

  ${({inError}) =>
    inError &&
    css`
      background: ${getColor('red', 10)};
      box-shadow: 0 0 0 1px ${getColor('red', 80)};
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
  closeTick?: boolean;
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
  closeTick = false,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);
  const searchRef = React.createRef<HTMLInputElement>();

  const focus = (ref: React.RefObject<HTMLInputElement>) => {
    ref.current?.focus();
  };

  React.useEffect(() => {
    if (isOpen) {
      focus(searchRef);
    }
  }, [isOpen]);

  React.useEffect(() => {
    isOpen ? close() : open();
  }, [closeTick]);

  React.useEffect(() => {
    close();
    handleSearchChange('');
  }, [value]);

  const handleSearchChange = (search: string) => {
    if (onSearchChange) onSearchChange(search);
  };

  const {readOnly} = React.useContext(TableInputContext);

  if (readOnly) {
    return <TableInputReadOnlyCell title={value}>{value}</TableInputReadOnlyCell>;
  }

  return (
    <SelectButtonDropdown {...rest}>
      <SelectButton
        onClick={(e: MouseEvent) => {
          e.preventDefault();
          open();
        }}
        tabIndex={-1}
        highlighted={highlighted}
        title={value}
        inError={inError}
      >
        {value}&nbsp;
      </SelectButton>
      <IconsPart>
        {value && !isOpen && (
          <IconButton
            icon={<CloseIcon />}
            size="small"
            title={clearLabel}
            ghost="borderless"
            level="tertiary"
            onClick={onClear}
          />
        )}
        <IconButton
          icon={<ArrowDownIcon />}
          size="small"
          title={openDropdownLabel}
          ghost="borderless"
          level="tertiary"
          onClick={open}
        />
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true} horizontalPosition="left">
          <Dropdown.Header>
            <Search
              inputRef={searchRef}
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
