import {Component} from 'react';
import {ErrorPage} from './pages/ErrorPage';

export class ErrorBoundary extends Component<unknown, {error?: Error}> {
  constructor(props: unknown) {
    super(props);
    this.state = {};
  }

  static getDerivedStateFromError(error: unknown) {
    return {error};
  }

  render() {
    if (this.state.error) {
      return <ErrorPage error={this.state.error} />;
    }

    return this.props.children;
  }
}
