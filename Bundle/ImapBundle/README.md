ImapBundle
==============

This bundle provides a functionality to work with email servers through IMAP protocol.

Dependencies
"zendframework/zend-mail": "2.1.*"

Notes: We cannot use more recent version of zend-mail because besimple/soap-bundle uses it as well and requires 2.1.* version.


Installation
------------

### Step 1) Get the bundle and the library

Add on composer.json (see http://getcomposer.org/)

    "require" :  {
        // ...
        "oro/imap-bundle": "dev-master",
    }

### Step 2) Register the bundle

To start using the bundle, register it in your Kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\ImapBundle\OroImapBundle(),
    );
    // ...
}
```

Usage
-----

``` php
<?php
    // Accessing IMAP connector factory
    /** @var $factory \Oro\Bundle\ImapBundle\Connector\ImapConnectorFactory */
    $factory = $this->get('oro_imap.connector.factory');

    // Creating IMAP connector for the ORO user
    /** @var $imap \Oro\Bundle\ImapBundle\Connector\ImapConnector */
    $imapConnector = $factory->createUserImapConnector($userId);

    // Creating the search query builder
    /** @var $queryBuilder \Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder */
    $queryBuilder = $imapConnector->getSearchQueryBuilder();

    // Building a search query
    $query = $queryBuilder
        ->from('test@test.com')
        ->subject('notification')
        ->get();

    // Request an IMAP server for find emails
    $emails = $ewsConnector->findItems('INBOX',$query);
```
