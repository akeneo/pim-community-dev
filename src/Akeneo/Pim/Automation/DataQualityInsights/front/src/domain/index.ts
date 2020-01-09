import Axis from "./Axis.interface";
import Rates from "./Rates.interface";
import Rate, {RANK_1, RANK_2, RANK_3, RANK_4, RANK_5} from "./Rate.interface";
import Recommendation from "./Recommendation.interface";
import Evaluation from "./Evaluation.interface";
import Family, {Attribute} from "./Family.interface";
import Product from "./Product.interface";
import WidgetElement, {createWidget} from "./Spellcheck/WidgetElement";
import EditorElement, {getEditorContent, setEditorContent} from "./Spellcheck/EditorElement";
import HighlightElement, {createHighlight} from "./Spellcheck/HighlightElement";
import MistakeElement from "./Spellcheck/MistakeElement";

export {
  Axis,
  Rate,
  RANK_1, RANK_2, RANK_3, RANK_4, RANK_5,
  Rates,
  Recommendation,
  Evaluation,
  Family,
  Attribute,
  Product,
  WidgetElement, createWidget,
  EditorElement, getEditorContent, setEditorContent,
  HighlightElement, createHighlight,
  MistakeElement
};
