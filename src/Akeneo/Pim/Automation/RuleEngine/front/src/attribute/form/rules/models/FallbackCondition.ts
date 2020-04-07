import Condition from "./Condition";

export default class FallbackCondition implements Condition {
  public json: any;

  constructor(json: any) {
    this.json = json;
  }

  static match(json: any): Condition | false {
    return new FallbackCondition(json);
  }

  public toJson(): any {
    return this.json;
  }
}
