<?php

function array_diff_assoc_recursive($arraya, $arrayb) {
  return array_filter($arraya, function($valuea) use ($arrayb) {
    return in_array($valuea, $arrayb, true);
  });
}

$array = [
    [
        "code" => "description",
        "type" => "attribute",
        "channel" => "ecommerce",
        "locale" => null,
    ],
    [
        "code" => "name",
        "type" => "attribute",
        "channel" => null,
        "locale" => null,
    ],
    [
        "code" => "category",
        "type" => "property",
        "channel" => null,
        "locale" => null,
    ],
    [
        "code" => "description",
        "type" => "attribute",
        "channel" => "ecommerce",
        "locale" => null,
    ],
    [
        "code" => "name",
        "type" => "attribute",
        "channel" => null,
        "locale" => null,
    ],
    [
        "code" => "category",
        "type" => "property",
        "channel" => null,
        "locale" => null,
    ],
];

var_dump(array_unique( array_diff_assoc_recursive( $array, array_unique( $array, SORT_REGULAR ) ) ));
