import React, {ComponentType, FunctionComponent, useEffect} from 'react';
import {useCatalogContext, useFetchSpellcheckTextAnalysis} from '../../../../../../../infrastructure/hooks';
import {useDispatch} from 'react-redux';
import {getEditorContent} from '../../../../../../helper';
import {debounce} from 'lodash';
import {WidgetProps} from '../Widget';

const CHANGE_MILLISECONDS_DELAY = 500;

interface WidgetWithSpellcheckDecoratorProps {}

const WidgetWithSpellcheckDecorator = <P extends WidgetProps>(
  WidgetComponent: ComponentType<P>
): FunctionComponent<WidgetWithSpellcheckDecoratorProps & P> => {
  return props => {
    const {widget} = props;
    const {locale} = useCatalogContext();
    const dispatchAction = useDispatch();
    const {dispatchTextAnalysis} = useFetchSpellcheckTextAnalysis(widget);

    useEffect(() => {
      const handleTextAnalysis = debounce(async () => {
        const content = getEditorContent(widget.editor);

        await dispatchTextAnalysis(content, locale as string);
      }, CHANGE_MILLISECONDS_DELAY);

      widget.editor.addEventListener('input', handleTextAnalysis);

      return () => {
        widget.editor.removeEventListener('input', handleTextAnalysis);
      };
    }, [widget.id, widget.editor, dispatchAction]);

    return <WidgetComponent {...(props as P)} />;
  };
};

export default WidgetWithSpellcheckDecorator;
