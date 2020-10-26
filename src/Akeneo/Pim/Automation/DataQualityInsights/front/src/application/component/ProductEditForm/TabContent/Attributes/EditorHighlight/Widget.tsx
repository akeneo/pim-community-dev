import React, {FunctionComponent} from 'react';
import HighlightsContainer from './HighlightsContainer';
import {WidgetElement} from '../../../../../helper';
import WidgetWithContextListenerDecorator from './WidgetWithContextListenerDecorator';
import WidgetWithPortalDecorator from './WidgetWithPortalDecorator';

export interface WidgetProps {
  widget: WidgetElement;
}

export interface NamedWidgetProps extends WidgetProps {
  name: string;
}

const BaseWidget: FunctionComponent<WidgetProps> = ({widget}) => {
  const {isVisible} = widget;

  return (
    <>
      {isVisible && (
        <div className="AknEditorHighlight-wrapper">
          <HighlightsContainer widget={widget} />
        </div>
      )}
    </>
  );
};

const WidgetWithContextListener: FunctionComponent<WidgetProps> = props => {
  return WidgetWithContextListenerDecorator(BaseWidget)({
    ...props,
  });
};

const Widget: FunctionComponent<NamedWidgetProps> = props => {
  return WidgetWithPortalDecorator(WidgetWithContextListener)({
    ...props,
    containerId: `editor-highlight-${props.name}-${props.widget.id}`,
  });
};

export default Widget;
