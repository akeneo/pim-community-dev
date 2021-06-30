import React, {ReactNode} from 'react';
import {Dropdown} from '../../../Dropdown/Dropdown';
import {useBooleanState} from '../../../../hooks';
import {ArrowDownIcon, CloseIcon} from '../../../../icons';
import {Search} from '../../../Search/Search';
import styled, {css} from 'styled-components';
import {IconButton} from '../../../IconButton/IconButton';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const SelectButtonDropdown = styled(Dropdown)`
  width: 100%;
  cursor: pointer;
`;

const SelectButton = styled.button<{highlighted: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  display: flex;
  justify-content: space-between;
  padding: 0 10px;
  height: 39px;
  line-height: 39px;
  align-items: center;
  cursor: pointer;

  ${({highlighted}) =>
    highlighted
      ? css`
          background: ${getColor('green', 10)};
          box-shadow: 0 0 0 1px ${getColor('green', 80)};
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
  fetchNextPage?: () => void;
};

const TableInputSelect: React.FC<TableInputSelectProps> = ({
  value,
  onClear,
  fetchNextPage,
  clearLabel,
  openDropdownLabel,
  highlighted = false,
  searchValue = '',
  searchPlaceholder,
  onSearchChange,
  searchTitle,
  onNextPage,
  children,
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
    <SelectButtonDropdown {...rest}>
      <SelectButton onClick={open} tabIndex={-1} highlighted={highlighted}>
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
