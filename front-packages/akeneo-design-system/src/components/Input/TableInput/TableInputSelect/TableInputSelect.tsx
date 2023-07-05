import React, {ReactElement, ReactNode} from 'react';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {useBooleanState} from '../../../../hooks';
import {ArrowDownIcon, CloseIcon} from '../../../../icons';
import {Search} from '../../../Search/Search';
import styled from 'styled-components';
import {IconButton} from '../../../IconButton/IconButton';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputReadOnlyCell} from '../shared/TableInputReadOnlyCell';
import {highlightCell} from '../shared/highlightCell';

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

  ${highlightCell};
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
  bottomHelper?: ReactElement;
  withSearch?: boolean;
  onOpenChange?: (isOpen: boolean) => void;
  children?: React.ReactNode;
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
  bottomHelper,
  withSearch = true,
  onOpenChange,
  ...rest
}) => {
  const [isOpen, open, close] = useBooleanState(false);
  const handleOpen = () => {
    open();
    onOpenChange?.(true);
  };
  const handleClose = () => {
    close();
    onOpenChange?.(false);
  };

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
    isOpen ? handleClose() : handleOpen();
  }, [closeTick]);

  React.useEffect(() => {
    handleClose();
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
          handleOpen();
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
          onClick={handleOpen}
        />
      </IconsPart>
      {isOpen && (
        <Dropdown.Overlay onClose={handleClose} dropdownOpenerVisible={true} horizontalPosition="left">
          {withSearch && (
            <Dropdown.Header>
              <Search
                inputRef={searchRef}
                onSearchChange={handleSearchChange}
                placeholder={searchPlaceholder}
                searchValue={searchValue}
                title={searchTitle}
              />
            </Dropdown.Header>
          )}
          <Dropdown.ItemCollection onNextPage={onNextPage}>{children}</Dropdown.ItemCollection>
          {bottomHelper}
        </Dropdown.Overlay>
      )}
    </SelectButtonDropdown>
  );
};

export {TableInputSelect};
