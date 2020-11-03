import {useCallback, useState} from 'react';
import {PopoverInitialState, PopoverState, usePopoverState} from 'reakit/Popover';
import {HighlightElement} from '../../../application/helper';
import {HighlightPopoverContextState} from '../../../application/context/HighlightPopoverContext';
import {HighlightPopoverProps} from '../../../application/component/Common/HighlightableContent/Popover/HighlightPopover';

export type HighlightPopoverActionState = {
  hide: () => void;
  show: () => void;
  setActiveElement: (element: HTMLElement | null) => void;
  setActiveHighlight: (highlight: HighlightElement | null) => void;
  ariaLabel: string;
  setAriaLabel: (label: string) => void;
};

export type HighlightPopoverState = PopoverState & HighlightPopoverContextState & HighlightPopoverActionState;

export type HighlightPopoverInitialState = PopoverInitialState & {};

export const useHighlightPopoverProps = (state: HighlightPopoverState): HighlightPopoverProps => {
  const {setActiveElement, setActiveHighlight, setAriaLabel, ...props} = state;

  return props;
};

const useHighlightPopoverState = (initialPopoverState?: HighlightPopoverInitialState): HighlightPopoverState => {
  const [visible, setVisible] = useState<boolean>(false);
  const [activeElement, setActiveElement] = useState<HTMLElement | null>(null);
  const [activeHighlight, setActiveHighlight] = useState<HighlightElement | null>(null);
  const [ariaLabel, setAriaLabel] = useState<string>('');

  const popoverState = usePopoverState({
    ...(initialPopoverState || {}),
    visible,
    modal: true,
  });

  const handleShow = useCallback(() => setVisible(true), []);
  const handleHide = useCallback(() => setVisible(false), []);

  return {
    ...popoverState,
    visible,
    show: handleShow,
    hide: handleHide,
    activeHighlight,
    setActiveHighlight,
    activeElement,
    setActiveElement,
    ariaLabel,
    setAriaLabel,
  };
};

export default useHighlightPopoverState;
