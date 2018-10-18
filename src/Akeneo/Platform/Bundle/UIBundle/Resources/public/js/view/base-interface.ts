import * as Backbone from 'backbone';

export default interface View extends Backbone.View<any> {
  readonly preUpdateEventName: string;
  readonly postUpdateEventName: string;
  code: string;
  zones: any;
  targetZone: string;
  position: number;
  setParent: (view: View) => void;
  getParent: () => View | null;
  configure: () => JQueryPromise<any>;
  shutdown: () => void;
  triggerExtensions: () => void;
  getFormData: () => any;
}
