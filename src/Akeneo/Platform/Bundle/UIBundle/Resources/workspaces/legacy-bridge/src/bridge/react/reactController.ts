import $ from 'jquery';
import {Events, mediator} from '@akeneo-pim-community/shared';
import {mountReactElementRef, unmountReactElementRef} from './reactElementHelper';

type ControllerOptions = {
  el?: HTMLElement | JQuery<HTMLElement>;
  [option: string]: any;
};

/**
 * Base class of the route controllers rendering a React application.
 *
 * It implements the controller contract expected by `pim/router` (constructor options, setActive, renderRoute,
 * canLeave, remove and the "pim:controller:can-leave" event) without depending on Backbone.
 */
abstract class ReactController {
  public readonly el: HTMLElement;
  public readonly $el: JQuery<HTMLElement>;
  public readonly options: ControllerOptions;
  public active: boolean = false;

  constructor(options: ControllerOptions = {}) {
    this.options = options;
    this.el = ReactController.toElement(options.el);
    this.$el = $(this.el);

    this.initialize(options);
  }

  /**
   * Base React element to mount (and keep as ref between route changes).
   */
  abstract reactElementToMount(): JSX.Element;

  /**
   * RegEx should match the base 'route' of the controller.
   * The goal in to avoid to mount/unmount React between route changes and keep the same React element ref while in the
   * controller/context.
   */
  abstract routeGuardToUnmount(): RegExp;

  /**
   * Return a static reference to a HTMLElement
   */
  getContainerRef(): Element {
    return this.el;
  }

  initialize(_options?: ControllerOptions): void {
    mediator.on('route_start', this.handleRouteChange, this);
  }

  renderRoute(_route?: any, _path?: string): JQueryPromise<void> {
    const container = mountReactElementRef(this.reactElementToMount(), this.getContainerRef());
    if (container !== this.el) {
      this.el.appendChild(container);
    }

    // A resolved jQuery promise (rather than a native one) keeps the router "route_complete" event synchronous
    return $.Deferred<void>().resolve().promise();
  }

  setActive(active: boolean): void {
    this.active = active;
  }

  canLeave(): boolean {
    const event = {canLeave: true};
    this.trigger('pim:controller:can-leave', event);

    return event.canLeave;
  }

  remove(): this {
    mediator.off('route_start', this.handleRouteChange, this);
    this.stopListening();
    this.$el.remove();
    this.setActive(false);

    return this;
  }

  /**
   * Avoid React mount/unmount between route changes.
   */
  private handleRouteChange(routeName: string) {
    if (true === this.routeGuardToUnmount().test(routeName)) {
      return;
    }

    unmountReactElementRef(this.getContainerRef());
  }

  private static toElement(el: ControllerOptions['el']): HTMLElement {
    if (undefined === el) {
      return document.createElement('div');
    }

    return el instanceof HTMLElement ? el : (el.get(0) as HTMLElement);
  }
}

// Event methods (on, off, trigger, listenTo...) compatible with the ones of the legacy Backbone controllers
interface ReactController extends Events {}
Object.assign(ReactController.prototype, Events);

export {ReactController};
