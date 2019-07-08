<?php

namespace Oro\Bundle\DotmailerBundle\Tests\Unit\Model;

use Http\Client\Common\HttpMethodsClient;
use Oro\Bundle\DotmailerBundle\Entity\DotmailerTransport;
use Oro\Bundle\DotmailerBundle\Model\OAuthManager;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OAuthManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $router;

    /**
     * @var SymmetricCrypterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $encryptor;

    /**
     * @var HttpMethodsClient|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $curlClient;

    /**
     * @var OAuthManager
     */
    protected $oAuthManager;

    protected function setUp()
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->encryptor = $this->createMock(SymmetricCrypterInterface::class);
        $this->curlClient = $this->createMock(HttpMethodsClient::class);
        $this->curlClient->expects($this->any())
            ->method('post')
            ->willReturn($this->createMock(MessageInterface::class));

        $this->oAuthManager = new OAuthManager($this->router, $this->encryptor, $this->curlClient);
    }

    public function testGetApiEndpoint()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);
        $transport->expects($this->once())
            ->method('getCustomDomain')
            ->will($this->returnValue(null));

        $expected = 'https://r1-app.dotmailer.com/';
        $actual = $this->oAuthManager->getApiEndpoint($transport);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAuthorizeUrl()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);

        $expected = OAuthManager::AUTHORISE_URL;
        $actual = $this->oAuthManager->getAuthorizeUrl($transport);
        $this->assertEquals($expected, $actual);
    }

    public function testGetTokenUrl()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);

        $expected = $this->oAuthManager->getApiEndpoint($transport) . 'OAuth2/Tokens.ashx';
        $actual = $this->oAuthManager->getTokenUrl($transport);
        $this->assertEquals($expected, $actual);
    }

    public function testGetLoginUserUrl()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);

        $expected = $this->oAuthManager->getApiEndpoint($transport) . '?oauthtoken=';
        $actual = $this->oAuthManager->getLoginUserUrl($transport);
        $this->assertEquals($expected, $actual);
    }

    public function testGetCallbackUrl()
    {
        $expected = '/oauth/callback';

        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                'oro_dotmailer_oauth_callback',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($expected);

        $actual = $this->oAuthManager->getCallbackUrl();
        $this->assertEquals($expected, $actual);
    }

    public function testGenerateAuthorizeUrl()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);
        $state = 'some string';

        $params = array(
            'redirect_uri'  => $this->oAuthManager->getCallbackUrl(),
            'response_type' => 'code',
            'scope'         => 'Account',
            'state'         => $state
        );
        $expected = $this->oAuthManager->getAuthorizeUrl($transport) .
            http_build_query($params) .
            '&client_id=' . $transport->getClientId();

        $actual = $this->oAuthManager->generateAuthorizeUrl($transport, $state);
        $this->assertEquals($expected, $actual);
    }

    public function testGenerateRefreshToken()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);
        $code = 'some string';

        $actual = $this->oAuthManager->generateRefreshToken($transport, $code);
        $this->assertFalse($actual);
    }

    public function testGenerateLoginUserUrl()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);
        $refreshToken = 'some string';

        $actual = $this->oAuthManager->generateLoginUserUrl($transport, $refreshToken);
        $this->assertFalse($actual);
    }

    public function testGenerateAccessToken()
    {
        /** @var DotmailerTransport|\PHPUnit\Framework\MockObject\MockObject $transport **/
        $transport = $this->createMock(DotmailerTransport::class);
        $refreshToken = 'some string';

        $actual = $this->oAuthManager->generateAccessToken($transport, $refreshToken);
        $this->assertFalse($actual);
    }
}
