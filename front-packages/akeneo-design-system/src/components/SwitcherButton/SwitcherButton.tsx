import React, {Ref, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {ArrowDownIcon, CloseIcon} from '../../icons';
import {AkeneoThemedProps, CommonStyle, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';

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
  ${({$inline}) =>
    $inline
      ? css`
          align-items: baseline;
        `
      : css`
          flex-direction: column;
        `}
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
          line-height: ${getFontSize('small')};
          margin-bottom: -2px;
        `}
`;

const LabelAndArrow = styled.div`
  display: inline-flex;
  align-items: center;
`;

const Value = styled.span<{$inline: boolean} & AkeneoThemedProps>`
  color: ${({$inline}) => ($inline ? getColor('purple', 100) : getColor('grey', 140))};
  margin-right: 5px;
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
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The label of the field
     */
    label: string;

    /**
     * The callback when the user clicks on the switcher button
     */
    onClick?: () => void;

    /**
     * Displays the composant in 1 or 2 lines.
     */
    inline?: boolean;

    /**
     * If true, the composant will display a second button to remove the component
     */
    deletable?: boolean;

    /**
     * The callback when the user clicks on the delete button
     */
    onDelete?: () => void;

    children?: ReactNode;
  }
>;

/**
 * Switchers are used to switch the filter on the context or the content of a page or a table.
 */
const SwitcherButton = React.forwardRef<HTMLDivElement, SwitcherButtonProps>(
  (
    {label, children, onClick, deletable = false, onDelete, inline = true, ...rest}: SwitcherButtonProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleDelete = () => deletable && onDelete?.();

    const handleClick = () => onClick?.();

    return (
      <SwitcherButtonContainer ref={forwardedRef} {...rest}>
        <LabelAndValueContainer onClick={handleClick} $inline={inline}>
          <Label $inline={inline}>{label && `${label}:`}</Label>
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
