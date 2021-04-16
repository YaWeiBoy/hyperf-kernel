<?php

declare (strict_types=1);
/**
 * @copyright 深圳市易果网络科技有限公司
 * @version 1.0.0
 * @link https://dayiguo.com
 */

namespace Zyw\HyperfKernel\Service;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Zyw\HyperfKernel\SMS\Exception\SMSException;
use Zyw\HyperfKernel\SMS\Exception\SMSIntervalException;
use Zyw\HyperfKernel\SMS\SMSFactory;
use Zyw\HyperfKernel\SMS\SMSInterface;

/**
 * 短信服务
 *
 * @author
 * @package Zyw\HyperfKernel\Service
 */
class SMSService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SMSInterface
     */
    private $SMSFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * SMSService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->cache      = $container->get(CacheInterface::class);
        $this->SMSFactory = $container->get(SMSFactory::class)->get();
    }

    /**
     * 设置发送渠道
     *
     * @param string|null $channel
     * @return $this
     */
    public function setChannel(string $channel = null): self
    {
        $this->SMSFactory = $this->container->get(SMSFactory::class)->get($channel);
        return $this;
    }

    /**
     * 发送验证码
     *
     * @param string $phone 手机号码
     * @param string $scene 场景
     * @param string $code 验证码
     * @param string $templateCode 短信模板Code
     * @return mixed
     * @throws SMSException
     * @throws SMSIntervalException
     */
    public function sendVerifyCode(string $phone, string $scene, string $code, string $templateCode)
    {
        // 获取缓存
        $cacheName = sprintf(config('sms.verify_code_cache'), $scene, $phone);
        try {
            if (($his = $this->cache->get($cacheName, null)) !== null) {
                // 是否开启发送频率限制
                if (($interval = config('sms.interval', 0)) > 0) {
                    // 判断发送频率
                    if (isset($his['setTime']) && $his['setTime'] + $interval > time()) {
                        throw new SMSIntervalException('SMS is sent too frequently');
                    }
                }
            }
            // 发送验证码
            $result = $this->SMSFactory->sendVerifyCode($phone, $code, $templateCode);
            $this->cache->set($cacheName, [
                'code'    => $code,
                'setTime' => time()
            ], config('sms.expired'));
            return $result;
        } catch (InvalidArgumentException $e) {
            throw new SMSException('Failed to send:' . $e->getMessage());
        }
    }

    /**
     * 校验验证码
     *
     * @param string $phone
     * @param string $scene
     * @param string $code
     * @return bool
     */
    public function checkVerifyCode(string $phone, string $scene, string $code): bool
    {
        // 获取缓存
        $cacheName = sprintf(config('sms.verify_code_cache'), $scene, $phone);
        try {
            if (!$cache = $this->cache->get($cacheName)) {
                return false;
            }
            if (!isset($cache['code']) || $cache['code'] !== $code) {
                return false;
            }
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * 销毁验证码
     *
     * @param string $phone
     * @param string $scene
     * @return bool
     */
    public function destroyVerifyCode(string $phone, string $scene): bool
    {
        // 获取缓存
        $cacheName = sprintf(config('sms.verify_code_cache'), $scene, $phone);
        try {
            $this->cache->delete($cacheName);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * 发送短信
     *
     * @param string $phone
     * @param string $templateCode
     * @param string $content
     * @return array
     */
    public function sendSMS(string $phone, string $templateCode, string $content): array
    {
        return $this->SMSFactory->sendSMS($phone, $templateCode, $content);
    }
}