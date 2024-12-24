<?php

namespace Lijian\Validator;

use DateTime;
use Exception;

use DateTime;
use Exception;

class Validate
{
	private static array $input = [];
	private static array $output = [];
	private static string|null $customException = null;
	private static int $errorCode = 300;
	protected array $rules = [];
	public static array $showAllRules = [
		'required' => '参数必填,可设置一个默认值',
	];
	
	public static function validateArray(array $input, $rules, $customException = null, $errorCode = 300): array
	{
		
		self::$customException = $customException ?: Exception::class;
		self::$input = $input;
		self::$output = [];
		self::applyRules($rules);
		return self::$output;
	}
	
	public static function validateOne(array $input, array $rules)
	{
		self::$input = $input;
		self::$output = [];
		self::applyRules($rules);
		return reset(self::$output);
	}
	
	private static function applyRules($rules)
	{
		foreach ($rules as $fieldName => $rule) {
			foreach ($rule->rules as $item) {
				$function = $item['function'];
				$fieldValue = self::$input[$fieldName] ?? null;
				self::$function($fieldName, $fieldValue, $item);
			}
		}
	}
	
	public static function rule(): Validate
	{
		return new static();
	}
	
	private function addRule(string $function, string $msg = '', $additionalParams = []): Validate
	{
		$this->rules[] = array_merge([
			'msg' => $msg,
			'function' => $function,
		], $additionalParams);
		return $this;
	}
	
	public function required($msg = '', $defaultValue = null): Validate
	{
		return $this->addRule('_required', $msg, ['defaultValue' => $defaultValue]);
	}
	
	private static function _required($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数必填:' . $fieldName;
		if ($fieldValue ?? null) {
			self::$output[$fieldName] = $fieldValue;
		} elseif ($item['defaultValue'] !== null) {
			self::$input[$fieldName] = $item['defaultValue'];
			self::$output[$fieldName] = $item['defaultValue'];
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function stringTrim($msg = ''): Validate
	{
		return $this->addRule('_stringTrim', $msg);
	}
	
	private static function _stringTrim($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不合法';
		if ($fieldValue) {
			self::$input[$fieldName] = trim($fieldValue);
			self::$output[$fieldName] = self::$input[$fieldName];
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function betweenNumber(int $min, int $max, $msg = ''): Validate
	{
		return $this->addRule('_betweenNumber', $msg, ['min' => $min, 'max' => $max]);
	}
	
	private static function _betweenNumber($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '必须在' . $item['min'] . '-' . $item['max'] . '之间';
		if ($fieldValue >= $item['min'] && $fieldValue <= $item['max']) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function inArray($array, $msg = ''): Validate
	{
		return $this->addRule('_inArray', $msg, ['array' => $array]);
	}
	
	private static function _inArray($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '仅允许在(' . implode(',', $item['array']) . ')中';
		if (in_array($fieldValue, $item['array'])) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isArray($msg = ''): Validate
	{
		return $this->addRule('_isArray', $msg);
	}
	
	private static function _isArray($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '必须是一个数组';
		if (is_array($fieldValue)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function notValidate(): Validate
	{
		return $this->addRule('_notValidate');
	}
	
	private static function _notValidate($fieldName, $fieldValue, $item)
	{
		self::$output[$fieldName] = $fieldValue ?? null;
	}
	
	public function isNumber($msg = ''): Validate
	{
		return $this->addRule('_isNumber', $msg);
	}
	
	private static function _isNumber($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '必须是数字';
		if (is_numeric($fieldValue)) {
			self::$output[$fieldName] = intval($fieldValue);
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function stringLength($min = 1, $max = 20, $msg = ''): Validate
	{
		return $this->addRule('_stringLength', $msg, ['min' => $min, 'max' => $max]);
	}
	
	private static function _stringLength($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '的长度必须在' . $item['min'] . '~' . $item['max'] . '之间';
		$length = mb_strlen($fieldValue, 'utf-8');
		if ($length >= $item['min'] && $length <= $item['max']) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isEmail($msg = ''): Validate
	{
		return $this->addRule('_isEmail', $msg);
	}
	
	private static function _isEmail($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的邮箱地址';
		$emailRegex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
		if (preg_match($emailRegex, $fieldValue)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isMobile($msg): Validate
	{
		return $this->addRule('_isMobile', $msg);
	}
	
	private static function _isMobile($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的手机号';
		$phoneRegex = '/^1[3-9]\d{9}$/';
		if (preg_match($phoneRegex, $fieldValue)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isDateTimeInFormat($format, $msg): Validate
	{
		return $this->addRule('_isDateTimeInFormat', $msg, ['format' => $format]);
	}
	
	private static function _isDateTimeInFormat($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的时间字符串.(' . $item['format'] . ')';
		$d = DateTime::createFromFormat($item['format'], $fieldValue);
		if ($d && $d->format($item['format']) === $fieldValue) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isIdCard($msg): Validate
	{
		return $this->addRule('_isIdCard', $msg);
	}
	
	private static function _isIdCard($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的身份证号';
		$idCardRegex = '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/';
		if (preg_match($idCardRegex, $fieldValue)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isUrl($msg): Validate
	{
		return $this->addRule('_isUrl', $msg);
	}
	
	private static function _isUrl($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的url';
		if (filter_var($fieldValue, FILTER_VALIDATE_URL)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
	
	public function isIp($msg, $type = 'ipv4'): Validate
	{
		return $this->addRule('_isIp', $msg, ['type' => $type]);
	}
	
	private static function _isIp($fieldName, $fieldValue, $item)
	{
		$msg = $item['msg'] ?: '参数:' . $fieldName . '不是一个合法的ip地址';
		$filter = $item['type'] === 'ipv6' ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4;
		if (filter_var($fieldValue, FILTER_VALIDATE_IP, $filter)) {
			self::$output[$fieldName] = $fieldValue;
		} else {
			throw new self::$customException($msg, self::$errorCode);
		}
	}
}
