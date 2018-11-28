<?php

$config = array(

    'admin' => array(
        'core:AdminPassword',
    ),

    'example-userpass' => array(
        'exampleauth:UserPass',
        'admin:admin' => array(
            'uid' => array('admin'),
        ),
        'julia:julia' => array(
            'uid' => array('julia'),
        ),
        'peter:peter' => array(
            'uid' => array('peter'),
        ),
        'mary:mary' => array(
            'uid' => array('mary'),
        ),
        'sandra:sandra' => array(
            'uid' => array('sandra'),
        ),
        'pamela:pamela' => array(
            'uid' => array('pamela'),
        ),
        'julia:julia' => array(
            'julien' => array('julien'),
        ),
    ),

);
