import React from "react";
import {useGetSpellcheckWidgetsList} from "../../../../../infrastructure/hooks";
import WidgetPortal from "./Spellchecker/WidgetPortal";

const WidgetsList = () => {
  const widgets = useGetSpellcheckWidgetsList();

  return (
    <>
      {widgets &&
      Object.entries(widgets).map(([identifier, widget]) => (
        <WidgetPortal key={identifier} widget={widget} />
      ))}
    </>
  );
};

export default WidgetsList;
