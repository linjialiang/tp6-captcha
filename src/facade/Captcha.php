<?php

namespace linjialiang\captcha\facade;

use think\Facade;

/**
 * Class Captcha
 * @package linjialiang\captcha\facade
 * @mixin \linjialiang\captcha\Captcha
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \linjialiang\captcha\Captcha::class;
    }
}
