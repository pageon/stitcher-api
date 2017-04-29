<?php

namespace Pageon\Stitcher\Exception;

class ValidationException extends \Exception
{
    public static function required($fields) {
        $fields = (array) $fields;
        $fieldsAsString = '`' . implode('`, `', $fields) . '`';

        return new self("Following fields are required: {$fieldsAsString}.");
    }
}
