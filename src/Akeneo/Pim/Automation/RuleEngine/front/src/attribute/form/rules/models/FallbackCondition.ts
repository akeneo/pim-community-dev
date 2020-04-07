import Condition from "./Condition";

export default class FallbackCondition implements Condition {
  public json: any;

  constructor(json: any) {
    this.json = json;
  }
}
