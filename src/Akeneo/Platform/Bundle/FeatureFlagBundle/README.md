# FeatureFlagBundle

Simple, stupid and yet flexible Feature Flags system for the Symfony world. If you consider introducing a new feature flag, please, take 30 minutes to read the tremendous article [Feature Toggles (aka Feature Flags)
](https://www.martinfowler.com/articles/feature-toggles.html) before. Feature flags are not an easy and small topic. They present great powers but come with many burdens.  

## Why an Akeneo bundle for that?

There are dozens of feature flags libraries for Symfony, React, PHP or Javascript. A few Symfony's ones have been evaluated. They all propose different advanced features that are, at the moment this documentation being written, not useful to us.

The aim of this bundle is not to replace those libraries. But rather to establish a simple and clear contract for our teams. If you want to use an existing library, feel free to do so. You just have to embed it in the contracts described here.

Note: the most promising library for Symfony is probably [FlagceptionBundle](https://github.com/bestit/flagception-bundle). Too bad it doesn't support toggling Symfony services.

## Feature flags' configuration

Feature flags are defined by a _key_, representing the feature, and a _service_ which answers to the question "Is this feature enabled?". 

```yaml
// config/packages/akeneo_feature_flag.yml

akeneo_feature_flag:
    feature_flags:
        - { feature: 'myCoolFeature', service: '@service_that_defines_if_myCoolFeature_feature_is_enabled' }
        - { feature: 'foo', service: '@service_that_defines_if_foo_feature_is_enabled' }
        - ...
```

The most important here is to decouple the decision point (the place where I need to know if a feature is enabled) from the decision logic (how do I know if this feature is enabled). 

Your feature flag service must respect the following contract:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

interface FeatureFlag
{
    public function isEnabled(): bool
}    
```

### Examples

Let's take a very simple example: we want to (de)activate the _Onboarder_ feature via an environment variable. All we have to do is to declare the following service:

```yaml
services:
    service_that_defines_if_myCoolFeature_feature_is_enabled:
        class: 'Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag'
        arguments:
            - '%env(bool:FLAG_MYCOOLFEATURE_ENABLED)%'
```

Behind the scenes, the very simple `EnvVarFeatureFlag` class is called:

```php
namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class EnvVarFeatureFlag implements FeatureFlag
{
    private $isEnabled;

    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
``` 

Another example. Imagine now you want to allow Akeneo people working at Nantes to access a beta `foo` feature. All you have to do is declare in your code a service that implements `Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag`.

```yaml
services:
    service_that_defines_if_foo_feature_is_enabled:
        class: 'Akeneo\My\Own\Namespace\FooFeatureFlag'
        arguments:
            - '@request_stack'
``` 

```php
namespace Akeneo\My\Own\Namespace;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

class FooFeatureFlag implements FeatureFlag
{
    private $akeneoIpAddress = //...
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    public function isEnabled(): bool
    {
        return $this->requestStack->getCurrentRequest()->getClientIp() === $this->$akeneoIpAddress; 
    }
}
```

### About feature flag driven by an environment variable

To ease developments, the _FeatureFlagBundle_ comes with the the implementation `Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag`. It allows to know if a feature is activated by checking an environment variable. When you want to use this class, all you have to do is to declare a service.

Please, name your environment variable `FLAG_FOO_ENABLED`, where `FOO` is obviously the name of your feature. Using this convention will allow us to enable all flags on a PR's CI. And thus you'll be able to test your feature even it's disabled on production (aka, if you have put `FLAG_FOO_ENABLED=0` in the `.env` file).

### About the frontend

Flags are of course also available for frontend. Behind the scenes, a backend route (called `feature_flag`) is called. It returns a JSON response answering if the feature is enabled or not. See the part _Knowing if a feature is enabled_ for more information.


## Using feature flags in your code

### Knowing if a feature is enabled

#### Backend

A service called `feature_flags` exists to determine if the feature you have configured in `config/packages/akeneo_feature_flag.yml` is enabled or not. This is the one and only backend entry point you have to use.

```php
$flags = $container->get('feature_flags');

if ($flags->isEnabled('myCoolFeature')) { //...
```

You can easily disable a route if your feature is disabled by using the `_feature` metadata:

```yaml
pim_analytics_system_info_index:
    path: /system_info
    defaults: { _controller: 'pim_analytics.controller.system_info:indexAction', _format: html, _feature: 'myCoolFeature' }
```

### Switching DIC services based on a feature flag

You can replace a specific DIC service by an alternative one based on a feature flag:

```yaml
services:
    My\Service:
        tags:
            - { name: feature_flags.is_enabled, feature: myCoolFeature, otherwise: 'My\NullService' }

    My\NullService: ~
```

#### Frontend

A service called `pim/feature-flags` exists to determine if the feature you have configured in `config/packages/akeneo_feature_flag.yml` is enabled or not. This is the one and only frontend entry point you have to use.

```js
const FeatureFlags = require("pim/feature-flags");
​
if (FeatureFlags.isEnabled("myCoolFeature")) { //...
```

You can easily disable a form extension if your feature is disabled by using the `feature` metadata:

```yaml
# form_extensions.yml

extensions:
  pim-menu-system-connection-settings:
    module: pim/menu/item
    parent: pim-menu-system-navigation-block
    feature: myCoolFeature
```

Same mechanism to disable a route if your feature is disabled:

```yaml
# requirejs.yml

config:
  config:
    pim/controller-registry:
      controllers:
        akeneo_connectivity_connection_settings_index:
          module: pim/controller/connectivity/connection/settings
          feature: myCoolFeature
``` 

### Short living feature flags

**Flags that will live from a few days to a few weeks.**

This happens typically when you develop a "small" feature bits by bits. At present, the feature is not ready to be presented to the end user, but with a few more pull requests and tests, this will be the case. 

For those use cases, we'll go simple. Inject the feature flags service (backend or frontend) in your code and branch with a straightforward `if/else`. 

**This way of working works only and only if you clean all those hideous conditionals when your feature is ready to use.** Otherwise, the code will quickly become hell of a maze with all flags setup by all different teams. 

**Also, please take extra care on the impact your flag could have on other teams' flags.** If it becomes tedious, please adopt the same strategy as for long living flags instead.

### Long living feature flags

**Flags that will live more than a few weeks.**

The standard use case for that are premium features, like the _Onboarder_. They will always be present in the code, but won't be enabled for everyone or everytime.

Those flags require extra attention. We must avoid crippling business code with `if/else` branching. Instead, we can use:
- [inversion of control](https://en.wikipedia.org/wiki/Inversion_of_control) and [Symfony's service factories](https://symfony.com/doc/current/service_container/factories.html) 
- [Symfony's synthetic services](https://symfony.com/doc/current/service_container/synthetic_services.html)
- the [strategy pattern](https://en.wikipedia.org/wiki/Strategy_pattern)
- ...

Of course, at some point, you'll need a `if/else` to branch the (de)activation of your feature. But the idea here is to bury it far from your business code. 
