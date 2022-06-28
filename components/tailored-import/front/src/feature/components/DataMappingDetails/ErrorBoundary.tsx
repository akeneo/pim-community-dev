import React from 'react';
import {InvalidAttributeTargetError, InvalidAttributeTargetPlaceholder} from './Attribute/error';
import {InvalidPropertyTargetError, InvalidPropertyTargetPlaceholder} from './Property/error';

type ErrorBoundaryProps = {};

class ErrorBoundary extends React.Component<ErrorBoundaryProps, {error: Error | null}> {
  constructor(props: ErrorBoundaryProps) {
    super(props);

    this.state = {error: null};
  }

  static getDerivedStateFromError(error: Error) {
    return {error};
  }

  render() {
    const {error} = this.state;

    if (null === error) return this.props.children;

    switch (true) {
      case error instanceof InvalidAttributeTargetError:
        return <InvalidAttributeTargetPlaceholder />;
      case error instanceof InvalidPropertyTargetError:
        return <InvalidPropertyTargetPlaceholder />;
      default:
        return <div>{error.message}</div>;
    }
  }
}

export {ErrorBoundary};
