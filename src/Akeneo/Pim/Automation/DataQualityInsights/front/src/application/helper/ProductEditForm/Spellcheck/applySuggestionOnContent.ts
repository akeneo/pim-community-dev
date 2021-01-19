import applySpellingSuggestionInterface from '../../Spellcheck/applySpellingSuggestion.interface';
import EditorElement, {setEditorContent} from '../../EditorHighlight/EditorElement';

const applySuggestionOnContent: applySpellingSuggestionInterface = (element, suggestion, content, start, end) => {
  setEditorContent(element as EditorElement, content, suggestion, start, end);
};

export default applySuggestionOnContent;
