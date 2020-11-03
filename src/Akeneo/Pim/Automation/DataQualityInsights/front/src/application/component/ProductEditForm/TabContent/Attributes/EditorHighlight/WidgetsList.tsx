import React from 'react';
import {useGetEditorHighlightWidgetsList} from '../../../../../../infrastructure/hooks';
import SpellcheckWidget from './Spellcheck/SpellcheckWidget';

const WidgetsList = () => {
  const widgets = useGetEditorHighlightWidgetsList();

  return (
    <>
      {widgets &&
        Object.entries(widgets).map(([identifier, widget]) => <SpellcheckWidget key={identifier} widget={widget} />)}
    </>
  );
};

export default WidgetsList;
