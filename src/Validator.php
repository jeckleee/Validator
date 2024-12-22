<?php

namespace Lijian\Validator;

use DateTime;
use Exception;

class Validator
{
    private static array $input = [];

    private static array $putout = [];

    private static ?object $custom_exception = null;

    public array $rules = [];

    public static array $show_all_rules = [
        'required' => '参数必填,可设置一个默认值',

    ];

    public static function array(array $input, $rules, $custom_exception = null): array
    {
        self::$custom_exception = $custom_exception ?: Exception::class;
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
        $msg = $item['msg'] ?: '参数必填:' . $field_name;
        if ($field_value ?? null) {
            self::$putout[$field_name] = $field_value;
        } else {
            if ($item['def_value'] !== null) {
                self::$input[$field_name] = $item['def_value'];
                self::$putout[$field_name] = $item['def_value'];
            } else {
                throw new self::$custom_exception($msg, 300);
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
    private static function _stringTrim($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不合法';
        if ($field_value) {
            self::$input[$field_name] = trim($field_value, " \t\n\r");
            self::$putout[$field_name] = self::$input[$field_name];
        } else {
            throw new self::$custom_exception($msg, 300);
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
    private static function _betweenNumber($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须在' . $item['min'] . '-' . $item['max'] . '之间';
        if ($field_value >= $item['min'] && $field_value <= $item['max']) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
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

    private static function _inArray($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '仅允许在(' . implode(',', $item['array']) . ')中';
        if (in_array($field_value, $item['array'])) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
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

    private static function _isArray($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须是一个数组';
        if (is_array($field_value)) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
        }

    }

    public function notValidate()
    {
        $this->rules[] = [
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    private static function _notValidate($field_name, $field_value, $item)
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
    private static function _isNumber($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '必须是数字';
        if (is_numeric($field_value)) {
            self::$putout[$field_name] = intval($field_value);
        } else {
            throw new self::$custom_exception($msg, 300);
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

    private static function _stringLength($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '的长度必须' . ($item['min'] === $item['max'] ? '为' . $item['min'] : $item['min'] . '~' . $item['max'] . '之间');

        $length = mb_strlen($field_value, 'utf-8');

        if ($length >= $item['min'] && $length <= $item['max']) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
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
    private static function _isEmail($field_name, $field_value, $item)
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不是一个合法的邮箱地址';

        $emailRegex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        $res = (bool)preg_match($emailRegex, $field_value);
        if ($res) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
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

    private static function _isMobile($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ? $item['msg'] : '参数:' . $field_name . '不是一个合法的手机号';

        $phoneRegex = '/^1[3-9]\d{9}$/';
        $res = (bool)preg_match($phoneRegex, $field_value);
        if ($res) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
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
    private static function _isDateTimeInFormat($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不是一个合法的时间字符串.(' . $item['format'] . ')';


        $d = DateTime::createFromFormat($item['format'], $field_value);

        if ($d && $d->format($item['format']) === $field_value) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
        }
    }


    public function isIdCard($msg): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    private static function _isIdCard($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不是一个合法的身份证号';

        $idCardRegex = '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/';
        $res = (bool)preg_match($idCardRegex, $field_value);
        if ($res) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
        }
    }

    public function isUrl($msg): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    private static function _isUrl($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不是一个合法的url';
        if (filter_var($field_value, FILTER_VALIDATE_URL)) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
        }
    }

    //验证ip地址
    public function isIp($msg, $type = 'ipv4'): Validator
    {
        $this->rules[] = [
            'msg' => $msg,
            'type' => $type,
            'function' => '_' . __FUNCTION__,
        ];
        return $this;
    }

    private static function _isIp($field_name, $field_value, $item): void
    {
        $msg = $item['msg'] ?: '参数:' . $field_name . '不是一个合法的ip地址';
        $filter = FILTER_FLAG_IPV4;
        if ($item['type'] == 'ipv6') {
            $filter = FILTER_FLAG_IPV6;
        }

        if (filter_var($field_value, $filter)) {
            self::$putout[$field_name] = $field_value;
        } else {
            throw new self::$custom_exception($msg, 300);
        }
    }


}


