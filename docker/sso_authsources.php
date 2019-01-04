<?php

$config = array(

    'admin' => array(
        'core:AdminPassword',
    ),

    'example-userpass' => array(
        'exampleauth:UserPass',
        'admin:admin' => array(
            'akeneo_uid' => array('admin'),
        ),
        'julia:julia' => array(
            'akeneo_uid' => array('julia'),
        ),
        'peter:peter' => array(
            'akeneo_uid' => array('peter'),
        ),
        'mary:mary' => array(
            'akeneo_uid' => array('mary'),
        ),
        'sandra:sandra' => array(
            'akeneo_uid' => array('sandra'),
        ),
        'pamela:pamela' => array(
            'akeneo_uid' => array('pamela'),
        ),
        'julien:julien' => array(
            'akeneo_uid' => array('julien'),
        ),
    ),

);
