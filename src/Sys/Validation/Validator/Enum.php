<?php namespace Sys\Validation\Validator;

class Enum implements ValidatorInterface
{
    public function base($data, $rule)
    {
        return true;
    }

    public function min($data, $rule)
    {
        return $data < $rule ? false : true;
    }

    public function max($data, $rule)
    {
        return $data > $rule ? false : true;
    }

    public function enum($data, $rule)
    {
        return is_array($rule) && in_array($data, $rule) ? true : false;
    }

}
