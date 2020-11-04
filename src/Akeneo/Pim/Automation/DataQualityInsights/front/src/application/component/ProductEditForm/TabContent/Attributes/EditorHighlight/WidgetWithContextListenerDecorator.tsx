import React, {ComponentType, FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {
  disableWidgetAction,
  enableWidgetAction,
  showWidgetAction,
  updateWidgetContent,
} from '../../../../../../infrastructure/reducer';
import {getEditorContent} from '../../../../../helper';
import {WidgetProps} from './Widget';

interface WidgetWithContextListenerDecoratorProps {}

const WidgetWithContextListenerDecorator = <P extends WidgetProps>(
  WidgetComponent: ComponentType<P>
): FunctionComponent<WidgetWithContextListenerDecoratorProps & P> => {
  return props => {
    const {widget} = props;
    const dispatchAction = useDispatch();

    useEffect(() => {
      const handleFocus = () => {
        dispatchAction(showWidgetAction(widget.id));
        dispatchAction(enableWidgetAction(widget.id));
      };

      const handleBlur = () => {
        dispatchAction(disableWidgetAction(widget.id));
      };

      const handleChange = () => {
        const content = getEditorContent(widget.editor);
        dispatchAction(updateWidgetContent(widget.id, content));
      };

      widget.editor.addEventListener('focus', handleFocus);
      widget.editor.addEventListener('blur', handleBlur);
      widget.editor.addEventListener('input', handleChange);

      return () => {
        widget.editor.removeEventListener('focus', handleFocus);
        widget.editor.removeEventListener('blur', handleBlur);
        widget.editor.removeEventListener('input', handleChange);
      };
    }, [widget.id, widget.editor, dispatchAction]);

    return <WidgetComponent {...(props as P)} />;
  };
};

export default WidgetWithContextListenerDecorator;
