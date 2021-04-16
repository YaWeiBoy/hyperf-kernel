<?php
/**
 * @copyright
 * @version 1.0.0
 * @link
 */

namespace Zyw\HyperfKernel\SMS;

use Zyw\HyperfKernel\SMS\Exception\SMSException;

/**
 * 短信工厂抽象类
 *
 * @author zyw
 * @package Zyw\HyperfKernel\SMS
 */
interface SMSInterface
{
    /**
     * 发送短信验证码
     *
     * @param string $phone
     * @param string $code
     * @param string $templateCode
     * @return array
     */
    public function sendVerifyCode(string $phone, string $code, string $templateCode): array;

    /**
     * 发送短信
     *
     * @param string $phoneNumber
     * @param string $templateCode
     * @param string $content
     * @return array
     * @throws SMSException
     */
    public function sendSMS(string $phoneNumber, string $templateCode, string $content): array;
}