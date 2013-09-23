ImapBundle
==============

This bundle provides a functionality to work with email servers through IMAP protocol.

Dependencies
------------

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
    // Preparing connection config
    $imapConfig = new ImapConfig('imap.gmail.com', 993, 'ssl', 'user', 'pwd');

    // Accessing IMAP connector factory
    /** @var $factory \Oro\Bundle\ImapBundle\Connector\ImapConnectorFactory */
    $factory = $this->get('oro_imap.connector.factory');

    // Creating IMAP connector for the ORO user
    /** @var $imap \Oro\Bundle\ImapBundle\Connector\ImapConnector */
    $imapConnector = $factory->createImapConnector($imapConfig);

    // Creating IMAP manager
    $imapManager = new ImapEmailManager($imapConnector);

    // Creating the search query builder
    /** @var $queryBuilder \Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder */
    $queryBuilder = $imapManager->getSearchQueryBuilder();

    // Building a search query
    $query = $queryBuilder
        ->from('test@test.com')
        ->subject('notification')
        ->get();

    // Request an IMAP server for find emails
    $imapManager->selectFolder('INBOX');
    $emails = $imapManager->findItems($query);
```

Synchronization with IMAP servers
---------------------------------
Email synchronization functionality is implemented in the following classes:

 - ImapEmailSynchronizer
 - ImapEmailSynchronizationProcessor
 - EmailSyncCommand
