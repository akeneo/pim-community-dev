import {Deferred} from 'jquery';
import {mountReactElementRef, unmoundReactElementRef} from './react-element-helper';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');

abstract class ReactController extends BaseController {
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

  initialize() {
    mediator.on('route_start', this.handleRouteChange, this);

    return super.initialize();
  }

  renderRoute() {
    this.el.append(mountReactElementRef(this.reactElementToMount()));

    return Deferred().resolve();
  }

  remove() {
    mediator.off('route_start', this.handleRouteChange, this);

    this.el.remove();

    return super.remove();
  }

  /**
   * Avoid React mount/unmount between route changes.
   */
  private handleRouteChange(routeName: string) {
    if (true === this.routeGuardToUnmount().test(routeName)) {
      return;
    }

    unmoundReactElementRef();
  }
}

export = ReactController;
