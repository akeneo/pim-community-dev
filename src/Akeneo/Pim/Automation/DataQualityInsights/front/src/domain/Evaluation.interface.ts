import Recommendation from "./Recommendation.interface";
import {Rate} from "./index";

export interface ProductEvaluation {
  [axis: string]: AxisEvaluation;
}

export interface AxisEvaluation {
  [channel: string]: {
    [locale: string]: Evaluation;
  };
}

export default interface Evaluation {
  rate?: string;
  recommendations: Recommendation[];
  rates: Rate[];
}
