<?php namespace Sys\Validation;

class Validator
{
    private $ruleConfig = array();
    private $defaultArr = array();

    private function parseRule($ruleConfig)
    {
        if (empty($ruleConfig)) {
            return array();
        }
        foreach ($ruleConfig as $paramName => $rule) {
            $rulesArr = explode('|', $rule, 2);
            $checkType = $rulesArr[0];
            $ruleStr = $rulesArr[1];
            $ruleArr = explode('|', $ruleStr);
            $ruleConfig = array();
            if ($checkType == 'enum') {
                $ruleConfig['rule']['enum'] = explode(',', $ruleArr[0]);
            } elseif ($checkType == 'custom') {
                $ruleConfig['rule'][$ruleArr[0]] = '';
            } else {
                $range = explode(',', $ruleArr[0]);
                $ruleConfig['rule']['min'] = $range[0];
                $ruleConfig['rule']['max'] = $range[1];
            }
            !empty($ruleArr[1]) && $ruleConfig['rule']['must'] = $ruleArr[1];
            isset($ruleArr[2]) && $ruleArr[2] != NULL && $ruleConfig['def']['default'] = $ruleArr[2];
            !empty($ruleArr[3]) && $ruleConfig['def']['tip'] = $ruleArr[3];
            $ruleConfig['rule']['base'] = '';
            $this->ruleConfig[$paramName] = array(
                'checkType' => $rulesArr[0],
                'checkRule' => isset($ruleConfig['rule']) ? $ruleConfig['rule'] : array(),
                'checkDef' => isset($ruleConfig['def']) ? $ruleConfig['def'] : array(),
            );
        }
        return $this->ruleConfig;
    }


    private function setDefault($paramName, $val)
    {
        $this->defaultArr[$paramName] = $val;
    }

    public function getDefaults()
    {
        return $this->defaultArr;
    }

    private function getDefault($paramName)
    {
        return isset($this->ruleConfig[$paramName]['checkDef']['default']) && $this->ruleConfig[$paramName]['checkDef']['default'] != NULL ? $this->ruleConfig[$paramName]['checkDef']['default'] : '';
    }

    private function getCustomTip($paramName)
    {
        return isset($this->ruleConfig[$paramName]['checkDef']['tip']) ? $this->ruleConfig[$paramName]['checkDef']['tip'] : '';
    }

    public function check($params, $ruleConfig)
    {
        if (empty($ruleConfig) || empty($params) || !is_array($ruleConfig)) {
            return true;
        }
        $this->parseRule($ruleConfig);
        foreach ($this->ruleConfig as $paramName => $rule) {
            $checkClass = "\\Sys\\Validation\\Validator\\" . ucfirst(strtolower($rule['checkType']));
            //echo $checkClass;
            if (!class_exists($checkClass)) {
                throw new \Exception("Validator Class  " . $checkClass . " is not support!");
            }
            $ValidatorObj = new  $checkClass();
            foreach ($rule['checkRule'] as $ruleType => $ruleVal) {
                if (!isset($params[$paramName])) {
                    if ($ruleType == "must" && $ruleVal == "*") {
                        $this->throwException($paramName);
                    } else {
                        $this->setDefault($paramName, $this->getDefault($paramName));
                        continue;
                    }
                }
                if ($ruleType == "must") {
                    continue;
                }
                if (!method_exists($ValidatorObj, $ruleType)) {
                    throw new \Exception("Validator Class  " . $checkClass . " method " . $ruleType . " is not support!");
                }
                $data = $params[$paramName];
                if (!$ValidatorObj->$ruleType($data, $ruleVal)) {
                    $this->throwException($paramName);
                }
            }
        }
        return true;

    }

    public function throwException($paramName, $msg = '')
    {
        $cunstomTip = $this->getCustomTip($paramName);
        $msg = !empty($cunstomTip) ? $cunstomTip : (!empty($msg) ? $msg : "param " . $paramName . " is error!");
        throw new \Exception($msg, 100003);
    }

}

