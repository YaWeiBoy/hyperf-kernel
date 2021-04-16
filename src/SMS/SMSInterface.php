<?php
/**
 * @copyright
 * @version 1.0.0
 * @link
 */

namespace Zywacd\HyperfKernel\SMS;

use Zywacd\HyperfKernel\SMS\Exception\SMSException;

/**
 * 短信工厂抽象类
 *
 * @author zywacd
 * @package Zywacd\HyperfKernel\SMS
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