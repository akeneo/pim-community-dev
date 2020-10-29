/* eslint-disable @typescript-eslint/no-var-requires */
import {Deferred} from 'jquery';
import {
  mountReactElementRef,
  unmountReactElementRef,
} from './reactElementHelper';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');

export type RouteParams = {
  name: string;
  route: {
    tokens: any;
    defaults: any;
    requirements: any;
    hosttokens: any;
    methods: any;
    schemes: any;
  };
  params: any;
};

export default abstract class ReactController extends BaseController {
  /**
   * Base React element to mount (and keep as ref between route changes).
   */
  abstract reactElementToMount(routeParams?: RouteParams): JSX.Element;

  /**
   * RegEx should match the base 'route' of the controller.
   * The goal in to avoid to mount/unmount React between route changes and keep the same React element ref while in the
   * controller/context.
   */
  abstract routeGuardToUnmount(): RegExp | false;

  initialize() {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    mediator.on('route_start', this.handleRouteChange, this);

    return super.initialize();
  }

  renderRoute(routeParams?: RouteParams) {
    this.$el.append(
      mountReactElementRef(this.reactElementToMount(routeParams))
    );
    return Deferred().resolve();
  }

  remove() {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    mediator.off('route_start', this.handleRouteChange, this);
    this.$el.remove();

    return super.remove();
  }

  /**
   * Avoid React mount/unmount between route changes.
   */
  private handleRouteChange(routeName: string) {
    const routeGuardToUnmount = this.routeGuardToUnmount();
    if (
      false !== routeGuardToUnmount &&
      true === routeGuardToUnmount.test(routeName)
    ) {
      return;
    }

    unmountReactElementRef();
  }
}
