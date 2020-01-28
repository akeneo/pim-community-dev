import React, {FunctionComponent} from "react";
import {useGetEditorHighlightWidgetsList} from "../../../../../../infrastructure/hooks";
import SpellcheckWidget from "./Spellcheck/SpellcheckWidget";
import SuggestedTitleWidget from "./SuggestedTitle/SuggestedTitleWidget";
import {WidgetElement} from "../../../../../helper";

interface WidgetFactoryProps {
  widget: WidgetElement;
}

const WidgetItem: FunctionComponent<WidgetFactoryProps> = ({widget}) => {
  return (widget.isMainLabel ? <SuggestedTitleWidget widget={widget} /> : <SpellcheckWidget widget={widget} />);
};

const WidgetsList = () => {
  const widgets = useGetEditorHighlightWidgetsList();

  return (
    <>
      {widgets &&
      Object.entries(widgets).map(([identifier, widget]) => (
        <WidgetItem key={identifier} widget={widget}/>
      ))}
    </>
  );
};

export default WidgetsList;
