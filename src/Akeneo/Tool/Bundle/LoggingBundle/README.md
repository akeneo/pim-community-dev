Purpose of this bundle is to improve observability practice through good logging practices.
Underlying principles are:
- AOP, that enables SOC principle.
- Code annotations that allows code enrichment in respect of SOC principles. It is also easier to maintain/control than "old" configuration/regexp based Aspects. 

# Context
This logging AOP/annotations based bundle is based on https://github.com/rfauglas/symfony_aop_poc, itself based on
https://github.com/schmittjoh/JMSAopBundle + doctrine annotations.
POC showed some limitations: it was not possible to injection annotations customizations.
Also since poc Php 8 was released, bringing native annotation solution, named php attributes: https://www.php.net/manual/fr/language.attributes.overview.php

# Principles
At Symfony compile time, code is parsed to collect service method/ parameter annotations.
There are two kinds of annotations that can be enabled on a service method:
- \#LoggingContext: used for context enrichment. Say you have a Product in method parameter, you want to track Product in subsequent message logs. LoggingContext interceptor will register a normalized  context for this parameter. such as ['product'=> ['id'=>'AAA', 'uuid'=> 'BBB']]
- \#AuditLog: used to generate an audit two log message, a info for incoming call and an info for outgoing call, exception will be  .

AutditLog is put on a method. Which means, we need to collect the annotations on methods. 
Then on each method call we need to inject this calling context to perform adequate.
This is the role of InterceptorLoad which  


We build a  struct:
$interceptors[ClassUtils::getUserClass($class->name)][$method->getName]=([$methodAnnotations],[$parameter->[paramAnnotations]]]


For the moment these attributes are parameterless but we can think of various evolutions:
- LoggingContext could take the prefix parameter, in case product is not an entity but a product id, we might provide a ['product'=>'id'] parameter (which might/should be defined as a constant on the entity...)
We can also think of an additional parameter flag saying we want to persist parameter after service call.

- AuditLog might take additional parameter flag to say if you want to include exception in case of errors. 
