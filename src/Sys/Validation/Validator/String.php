<?php namespace Sys\Validation\Validator;

class String implements ValidatorInterface {
        public function base($data, $rule) {
                return is_string($data) ? true : false;
        }
        public function min($data, $rule) {
                return $this->strLen($data) < $rule ? false : true;
        }
        public function max($data, $rule) {
                return $this->strLen($data) > $rule ? false : true;
        }
	public function strLen($str) {
		return mb_strlen($str, 'UTF-8'); 
	}
}
