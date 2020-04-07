import Action from "./Action";

export default class FallbackAction implements Action {
  public json: any;

  constructor(json: any) {
    this.json = json;
  }
}
