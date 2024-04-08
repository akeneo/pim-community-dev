import React, {Ref, ReactNode, HTMLAttributes, forwardRef} from 'react';
import styled, {css} from 'styled-components';
import {ArrowDownIcon, CloseIcon} from '../../icons';
import {AkeneoThemedProps, CommonStyle, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {useId} from '../../hooks';

const SwitcherButtonContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  > *:nth-child(2) {
    opacity: 0;
    transition: opacity 0.2s;
  }
  &:hover > *:nth-child(2) {
    opacity: 1;
  }
`;

const LabelAndValueContainer = styled.button<{$inline: boolean} & AkeneoThemedProps>`
  ${CommonStyle};
  border: none;
  background: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: baseline;
  flex-direction: ${({$inline}) => ($inline ? 'row' : 'column')};
`;

const Label = styled.label<{$inline: boolean} & AkeneoThemedProps>`
  cursor: pointer;
  white-space: nowrap;
  ${({$inline}) =>
    $inline
      ? css`
          margin-right: 3px;
          color: ${getColor('grey', 140)};
        `
      : css`
          color: ${getColor('grey', 100)};
          text-transform: uppercase;
          font-size: ${getFontSize('small')};
        `}
`;

const LabelAndArrow = styled.div`
  display: inline-flex;
  align-items: center;
`;

const Value = styled.span<{$inline: boolean} & AkeneoThemedProps>`
  color: ${({$inline}) => ($inline ? getColor('brand', 100) : getColor('grey', 140))};
  margin-right: 5px;
  text-align: left;
`;

const CloseButton = styled.button`
  border: none;
  background: none;
  width: 20px;
  height: 20px;
  cursor: pointer;
  padding: 0;
  flex-shrink: 0;
`;

type SwitcherButtonProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The label of the field.
     */
    label: string;

    /**
     * The callback when the user clicks on the switcher button.
     */
    onClick?: () => void;

    /**
     * Displays the composant in 1 or 2 lines.
     */
    inline?: boolean;

    /**
     * If true, the composant will display a second button to remove the component.
     */
    deletable?: boolean;

    /**
     * The callback when the user clicks on the delete button.
     */
    onDelete?: () => void;

    children?: ReactNode;
  }
>;

/**
 * Switchers are used to switch the filter on the context or the content of a page or a table.
 */
const SwitcherButton = forwardRef<HTMLDivElement, SwitcherButtonProps>(
  (
    {label, children, onClick, deletable = false, onDelete, inline = true, ...rest}: SwitcherButtonProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const buttonId = useId('button_');

    const handleDelete = () => deletable && onDelete?.();

    return (
      <SwitcherButtonContainer ref={forwardedRef} {...rest}>
        <LabelAndValueContainer type="button" id={buttonId} onClick={onClick} $inline={inline}>
          <Label htmlFor={buttonId} $inline={inline}>
            {label ? (inline ? `${label}:` : label) : ''}
          </Label>
          <LabelAndArrow>
            <Value $inline={inline}>{children}</Value>
            <ArrowDownIcon size={inline ? 16 : 10} />
          </LabelAndArrow>
        </LabelAndValueContainer>
        {deletable && (
          <CloseButton onClick={handleDelete}>
            <CloseIcon size={10} />
          </CloseButton>
        )}
      </SwitcherButtonContainer>
    );
  }
);

export {SwitcherButton};
