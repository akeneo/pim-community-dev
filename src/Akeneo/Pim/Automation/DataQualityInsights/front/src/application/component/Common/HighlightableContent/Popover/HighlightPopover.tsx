import React, {FC, useRef} from 'react';
import {Popover, PopoverProps} from 'reakit/Popover';
import {HighlightPopoverContextProvider} from '../../../../context/HighlightPopoverContext';
import useHighlightPopoverPosition from '../../../../../infrastructure/hooks/Common/useHighlightPopoverPosition';
import {HighlightElement} from '../../../../helper';

export type HighlightPopoverProps = PopoverProps & {
  activeElement: HTMLElement | null;
  activeHighlight: HighlightElement | null;
  ariaLabel: string;
};

const HighlightPopover: FC<HighlightPopoverProps> = ({
  children,
  activeHighlight,
  activeElement,
  ariaLabel,
  ...popoverProps
}) => {
  const {hide, visible} = popoverProps;
  const popoverRef = useRef<HTMLDivElement>(null);
  const popoverPosition = useHighlightPopoverPosition(activeHighlight, popoverRef, visible || false);

  return (
    <HighlightPopoverContextProvider
      activeHighlight={activeHighlight}
      activeElement={activeElement}
      hide={hide !== undefined ? hide : () => {}}
    >
      <Popover
        {...popoverProps}
        ref={popoverRef}
        aria-label={ariaLabel}
        tabIndex={0}
        unstable_autoFocusOnShow={false}
        unstable_autoFocusOnHide={false}
        className="AknEditorHighlight-popover AknEditorHighlight-popover--visible"
        style={{
          position: 'absolute',
          ...popoverPosition,
        }}
      >
        {children}
      </Popover>
    </HighlightPopoverContextProvider>
  );
};

export default HighlightPopover;
