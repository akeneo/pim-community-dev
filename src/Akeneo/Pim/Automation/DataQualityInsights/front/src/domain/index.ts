import Axis, {AxesCollection} from "./Axis.interface";
import Rates from "./Rates.interface";
import Rate, {
  RANK_1, RANK_2, RANK_3, RANK_4, RANK_5,
  RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR, NO_RATE_COLOR
} from "./Rate.interface";
import Recommendation from "./Recommendation.interface";
import Evaluation from "./Evaluation.interface";
import Family, {Attribute} from "./Family.interface";
import Product from "./Product.interface";
import WidgetElement, {createWidget, WidgetsCollection} from "./Spellcheck/WidgetElement";
import EditorElement, {getEditorContent, setEditorContent} from "./Spellcheck/EditorElement";
import HighlightElement, {createHighlight, HighlightsCollection} from "./Spellcheck/HighlightElement";
import MistakeElement from "./Spellcheck/MistakeElement";

export {
  Axis, AxesCollection,
  Rate,
  RANK_1, RANK_2, RANK_3, RANK_4, RANK_5,
  RANK_1_COLOR, RANK_2_COLOR, RANK_3_COLOR, RANK_4_COLOR, RANK_5_COLOR, NO_RATE_COLOR,
  Rates,
  Recommendation,
  Evaluation,
  Family,
  Attribute,
  Product,
  WidgetElement, createWidget, WidgetsCollection,
  EditorElement, getEditorContent, setEditorContent,
  HighlightElement, createHighlight, HighlightsCollection,
  MistakeElement
};
