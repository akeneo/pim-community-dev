import React, {useEffect, useState} from "react";
import {createPortal} from "react-dom";
import SpellCheckerPopover from "./SpellCheckerPopover";

const CONTAINER_ELEMENT_ID = 'akeneo-spellchecker-popover-root';

const SpellCheckerPopoverPortal = () => {
  const [popoverContainer, setPopoverContainer] = useState<Element|null>(null);

  useEffect(() => {
    const element = document.createElement("div");
    element.id = CONTAINER_ELEMENT_ID;
    setPopoverContainer(element);

    document.body.appendChild(element);

    return () => {
      document.body.removeChild(element);
    };
  }, []);

  return (
    <>
      {popoverContainer && createPortal(<SpellCheckerPopover/>, popoverContainer)}
    </>
  );
};

export default SpellCheckerPopoverPortal;
