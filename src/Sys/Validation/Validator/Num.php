<?php namespace Sys\Validation\Validator;

class Num implements ValidatorInterface
{
    public function base($data, $rule)
    {
        return is_numeric($data) ? true : false;
    }

    public function min($data, $rule)
    {
        return $data < $rule ? false : true;
    }

    public function max($data, $rule)
    {
        return $data > $rule ? false : true;
    }
}
