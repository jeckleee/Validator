<?php

namespace Lijian\Validator;

use DateTime;
use Exception;

class Validator
{
    private static array $input = [];

    private static array $putout = [];

    public array $rules = [];

    public static function array(array $input, $rules): array
    {

        self::$input = $input;
        self::$putout = [];
        foreach ($rules as $key => $rule) {
            $field_name = $key;
            foreach ($rule->rules as $item) {
                $function = $item['function'];
                $field_value = self::$input[$field_name] ?? null;
                self::$function($field_name, $field_value, $item);
            }
        }
        return self::$putout;
    }

    public static function one(array $input, array $rules)
    {
        self::$input = $input;
        self::$putout = [];
        foreach ($rules as $key => $rule) {
            $field_name = $key;
            foreach ($rule->rules as $item) {
                $function = $item['function'];
                $field_value = self::$input[$field_name] ?? null;
                self::$function($field_name, $field_value, $item);
            }
            return self::$putout[$field_name];
        }
        return null;
    }


    public static function rule(): Validator
    {
        return new static();
    }

    public function required($msg = '', $def_value = null): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
            'def_value' => $def_value,
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    private static function _required($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数必填:' . $field_name;
        if ($field_value ?? null) {
            self::$putout[$field_name] = $field_value;
        } else {
            if ($item['def_value'] !== null) {
                self::$input[$field_name] = $item['def_value'];
                self::$putout[$field_name] = $item['def_value'];
            } else {
                throw new Exception($msg, 300);
            }
        }

    }

    function stringTrim($msg = ''): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function _stringTrim($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '不合法';
        if ($field_value) {
            self::$input[$field_name] = trim($field_value, " \t\n\r");
            self::$putout[$field_name] = self::$input[$field_name];
        } else {
            throw new Exception($msg, 300);
        }
    }

    public function betweenNumber(int $min, int $max, $msg = ''): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
            'min' => $min,
            'max' => $max
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function _betweenNumber($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须在' . $item['min'] . '-' . $item['max'] . '之间';
        if ($field_value >= $item['min'] && $field_value <= $item['max']) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }
    }

    public function inArray($array, $msg = ''): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
            'array' => $array
        ];
        return $this;

    }

    public static function _inArray($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '仅允许在(' . implode(',', $item['array']) . ')中';
        if (in_array($field_value, $item['array'])) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }

    }

    public function isArray($msg = '')
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    public static function _isArray($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须是一个数组';
        if (is_array($field_value)) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }

    }

    public function notValidate()
    {
        $this->rules[] = [
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    public static function _notValidate($field_name, $field_value, $item)
    {
        self::$putout[$field_name] = $field_value ?? null;
    }

    public function isNumber($msg = ''): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;

    }

    /**
     * @throws Exception
     */
    public static function _isNumber($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须是数字';
        if (is_numeric($field_value)) {
            self::$putout[$field_name] = intval($field_value);
        } else {
            throw new Exception($msg, 300);
        }

    }


    public function stringLength($min = 1, $max = 20, $msg = ''): Validator
    {

        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
            'min' => $min,
            'max' => $max
        ];
        return $this;

    }

    public static function _stringLength($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '的长度必须在' . ($item['min'] === $item['max'] ? $item['min'] : $item['min'] . '~' . $item['max']) . '之间';
        //求字符串$field_value的长度
        $length = mb_strlen($field_value, 'utf-8');

        if ($length >= $item['min'] && $length <= $item['max']) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }

    }

    //验证电子邮件
    public function isEmail($msg = ''): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function _isEmail($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '不是一个合法的邮箱地址';

        $emailRegex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        $res = (bool)preg_match($emailRegex, $field_value);
        if ($res) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }
    }

    public function isMobile($msg): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    public static function _isMobile($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '不是一个合法的手机号';

        $phoneRegex = '/^(13[0-9]|14[5-9]|15[0-3,5-9]|16[2,5,6,7]|17[0-8]|18[0-9]|19[0-3,5-9])\d{8}$/';
        $res = (bool)preg_match($phoneRegex, $field_value);
        if ($res) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }
    }

    public function isDateTimeInFormat($format, $msg): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
            'format' => $format,//Y-m-d H:i:s | Y-m-d | .....
        ];
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function _isDateTimeInFormat($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '不是一个合法的时间字符串.(' . $item['format'] . ')';


        $d = DateTime::createFromFormat($item['format'], $field_value);

        if ($d && $d->format($item['format']) === $field_value) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new Exception($msg, 300);
        }
    }
}


