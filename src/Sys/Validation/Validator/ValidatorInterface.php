<?php namespace Sys\Validation\Validator;

interface ValidatorInterface
{
    public function base($data, $rule);

    public function min($data, $rule);

    public function max($data, $rule);

}

