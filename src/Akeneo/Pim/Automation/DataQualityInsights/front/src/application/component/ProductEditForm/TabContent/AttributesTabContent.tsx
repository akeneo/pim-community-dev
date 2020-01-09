import React, {FunctionComponent} from "react";
import WidgetsList from "./Attributes/WidgetsList";
import SpellCheckerPopover from "./Attributes/SpellCheckerPopover";
import AttributesContextProvider from "../../../../infrastructure/context-provider/AttributesContextProvider";

export interface AttributesTabContentProps {}

const AttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <>
      <AttributesContextProvider />

      <WidgetsList/>
      <SpellCheckerPopover/>
    </>
  );
};

export default AttributesTabContent;
