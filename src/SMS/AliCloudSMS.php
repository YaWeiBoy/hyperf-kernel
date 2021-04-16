<?php

declare (strict_types=1);
/**
 * @copyright
 * @version 1.0.0
 * @link
 */

namespace Zywacd\HyperfKernel\SMS;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;
use Zywacd\HyperfKernel\SMS\Exception\SMSException;

/**
 * 阿里云短信服务
 *
 * @property string $accessKeyId
 * @property string $accessSecret
 * @property string $regionId
 * @property string $host
 * @property string $signName
 * @author zywacd
 * @package Zywacd\HyperfKernel\SMS
 */
class AliCloudSMS implements SMSInterface
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
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->config->get('sms.channel.aliCloud.' . $name, null);
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
        $content = Json::encode([
            'code' => $code
        ]);
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
        try {
            AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessSecret)
                ->regionId($this->regionId)
                ->asDefaultClient();

            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host($this->host)
                ->options([
                    'query' => [
                        'RegionId'      => $this->regionId,
                        'PhoneNumbers'  => $phoneNumber,
                        'SignName'      => $this->signName,
                        'TemplateCode'  => $templateCode,
                        'TemplateParam' => $content
                    ],
                ])
                ->request()
                ->toArray();
            // 判断是否发送失败
            if (!isset($result['Code']) || $result['Code'] !== 'OK') {
                throw new SMSException(sprintf('SMS failed to send, return result: %s', $result['Message'] ?? 'null'));
            }
            return $result;
        } catch (ClientException $e) {
            throw new SMSException(sprintf('ClientException: %s', $e->getMessage()), $e->getCode());
        } catch (ServerException $e) {
            throw new SMSException(sprintf('ServerException: %s', $e->getMessage()), $e->getCode());
        }
    }
}