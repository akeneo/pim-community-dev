export type NormalizedData = any;

/**
 * @api
 */
export default abstract class Data {
  public abstract normalize(): any;
  public abstract isEmpty(): boolean;
  public abstract equals(data: Data): boolean;
}
