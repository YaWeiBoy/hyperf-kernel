<?php

declare (strict_types=1);
/**
 * @copyright
 * @version 1.0.0
 * @link
 */

namespace Zywacd\HyperfKernel\SMS;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;
use Zywacd\HyperfKernel\SMS\Exception\SMSException;
use Exception;

/**
 * 聚合数据短信服务
 *
 * @property string $key
 * @author 刘兴永(aile8880@qq.com)
 * @package Zywacd\HyperfKernel\SMS
 */
class JuheSMS implements SMSInterface
{
    /**
     * @var string
     */
    const REQUEST_URL = 'http://v.juhe.cn/sms/send';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ClientFactory
     */
    private $guzzle;

    /**
     * SMSFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config    = $container->get(ConfigInterface::class);
        $this->guzzle    = $container->get(ClientFactory::class);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->config->get('sms.channel.juhe.' . $name, null);
    }

    /**
     * 发送短信验证码
     *
     * @param string $phone
     * @param string $code
     * @param string $templateCode
     * @return array
     */
    public function sendVerifyCode(string $phone, string $code, string $templateCode): array
    {
        $content = urlencode(sprintf('#code#=%s', $code));
        return $this->sendSMS($phone, $templateCode, $content);
    }

    /**
     * 发送短信
     *
     * @param string $phoneNumber
     * @param string $templateCode
     * @param string $content
     * @return array
     * @throws SMSException
     */
    public function sendSMS(string $phoneNumber, string $templateCode, string $content): array
    {
        $guzzle = $this->guzzle->create();
        try {
            $response = $guzzle->get(self::REQUEST_URL, [
                'query' => [
                    'key'       => $this->key,
                    'mobile'    => $phoneNumber,
                    'tpl_id'    => $templateCode,
                    'tpl_value' => $content
                ]
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new SMSException(sprintf('Response status code is abnormal: %s', $response->getStatusCode()));
            }
            $responseContents = $response->getBody()->getContents();
            $result           = Json::decode($responseContents, true);
            if (!isset($result['error_code']) || (int)$result['error_code'] !== 0) {
                throw new SMSException(sprintf('SMS failed to send, return result: %s', $responseContents));
            }
            return $result;
        } catch (Exception $e) {
            throw new SMSException(sprintf('ServerException: %s', $e->getMessage()), $e->getCode());
        }
    }
}