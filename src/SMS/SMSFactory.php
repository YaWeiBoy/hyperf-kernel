<?php

declare (strict_types=1);
/**
 * @copyright 深圳市易果网络科技有限公司
 * @version 1.0.0
 * @link https://dayiguo.com
 */

namespace Zyw\HyperfKernel\SMS;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Zyw\HyperfKernel\SMS\Exception\SMSException;

/**
 * 短信工厂
 *
 * @author
 * @package Zyw\HyperfKernel\SMS
 */
class SMSFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * SMSFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config    = $container->get(ConfigInterface::class);
    }

    /**
     * @param string|null $channel
     * @return mixed
     */
    public function get(string $channel = null): ?SMSInterface
    {
        $channel  = $channel === null ? $this->config->get('sms.default') : $channel;
        $channels = $this->config->get('sms.channel', []);
        if (!isset($channels[$channel])) {
            throw new SMSException(sprintf('SMS driver [%s] does not exist', $channel));
        }
        if (!class_exists($channels[$channel]['driver'])) {
            throw new SMSException(sprintf('[Error] class %s is invalid.', $channels[$channel]['driver']));
        }
        return $this->container->get($channels[$channel]['driver']);
    }
}