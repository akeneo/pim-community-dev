import React, {FunctionComponent} from "react";
import WidgetsList from "./Attributes/WidgetsList";
import AttributesContextProvider from "../../../../infrastructure/context-provider/AttributesContextProvider";
import SpellCheckerPopoverPortal from "./Attributes/SpellCheckerPopoverPortal";

export interface AttributesTabContentProps {}

const AttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <>
      <AttributesContextProvider />

      <WidgetsList/>
      <SpellCheckerPopoverPortal/>
    </>
  );
};

export default AttributesTabContent;
