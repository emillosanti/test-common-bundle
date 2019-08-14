<?php

namespace SAM\CommonBundle\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Stores\RepositoryStore;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use SAM\CommonBundle\Manager\MailManager;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class WebTestCase
 * @package Tests
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Container
     */
    protected static $container;

    /**
     * @var EntityManager|null
     */
    protected static $entityManager;

    /**
     * @var FactoryMuffin
     */
    protected static $factory;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \League\FactoryMuffin\Exceptions\DirectoryNotFoundException
     */
    public static function setUpBeforeClass()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        static::$container = $container;
        $entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        static::$entityManager = $entityManager;
        static::$factory = new FactoryMuffin(new RepositoryStore($entityManager));
        static::$factory->loadFactories(__DIR__ . '/factories');
        static::regenerateSchema();
    }

    /**
     *
     */
    public function setUp()
    {
        $routerScheme = $this->getContainer()->getParameter('router.request_context.scheme');
        $this->client = static::createClient([], ['HTTPS' => $routerScheme == 'https']);

        $mailManager = $this->getMockBuilder(MailManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::$container->set(MailManager::class, $mailManager);
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function tearDown()
    {
        parent::tearDown();
        self::$entityManager->clear();
    }

    /**
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function tearDownAfterClass()
    {
        static::regenerateSchema(false);
    }

    /**
     * @param array $request
     * @param Crawler|null $crawler
     * @return Response
     * @throws \Exception
     */
    protected function sendRequest(array $request, Crawler &$crawler = null): Response
    {
        $this->client->enableProfiler();
        ob_start();
        $crawler = $this->client->request(...$this->buildRequest($request));
        ob_end_clean();
        return $this->client->getResponse();
    }

    /**
     * @param array $request
     * @return array
     * @throws \Exception
     */
    protected function buildRequest(array $request): array
    {
        if (!key_exists('uri', $request)) {
            throw new \Exception('URI is not defined');
        }

        return [
            key_exists('method', $request) ? $request['method'] : 'GET',
            $request['uri'],
            key_exists('parameters', $request) ? $request['parameters'] : [],
            key_exists('files', $request) ? $request['files'] : [],
            key_exists('server', $request) ? $request['server'] : []
        ];
    }

    /**
     * @param Form $form
     * @return Response
     */
    protected function submit(Form $form): Response
    {
        $this->client->submit($form);
        return $this->client->getResponse();
    }

    /**
     * @return Container
     */
    protected function getContainer(): Container
    {
        return static::$container;
    }

    /**
     * @return EntityManager
     * @throws \Exception
     */
    protected function getEntityManager(): EntityManager
    {
        return static::$entityManager;
    }

    /**
     * @param array $request
     * @param array|null $roles
     * @throws \Exception
     */
    public function assertRequestSuccess(array $request, array $roles = null, $message = null)
    {
        $this->logIn(null, $roles);
        $response = $this->sendRequest($request);
        if ($response instanceof StreamedResponse) {
            $this->assertTrue($response->getStatusCode() == 200, $message);
        } else {
            $this->assertTrue($response->isSuccessful(), $message);
        }
    }

    public function assertRequestNotSuccess(array $request, array $roles = null, $message = null)
    {
        $this->logIn(null, $roles);
        $response = $this->sendRequest($request);
        $this->assertFalse($response->isSuccessful(), $message);
    }

    /**
     * @param array $request
     * @param string $redirectRegExp
     * @param array|null $roles
     * @throws \Exception
     */
    public function assertRequestRedirect(array $request, string $redirectRegExp, array $roles = null, $message = null)
    {
        $this->logIn(null, $roles);
        $response = $this->sendRequest($request);
        $this->assertTrue($response->isRedirect(), $message);
        $this->assertRegExp($redirectRegExp, $response->headers->get('location'), $message);
    }

    /**
     * @param array $request
     * @param int $status
     * @param array|null $roles
     * @throws \Exception
     */
    public function assertRequestStatus(array $request, int $status, array $roles = null)
    {
        $this->logIn(null, $roles);
        $response = $this->sendRequest($request);
        $this->assertEquals($status, $response->getStatusCode());
    }

    /**
     * @param array $request
     * @param array|null $roles
     * @throws \Exception
     */
    public function assertRequestNotAllowed(array $request, array $roles = null)
    {
        $this->assertRequestStatus($request, 403, $roles);
    }

    /**
     * @param array $request
     * @throws \Exception
     */
    public function assertRequestRedirectLogin(array $request)
    {
        $response = $this->sendRequest($request);

        $this->assertTrue($response->isRedirect());
        $this->assertRegExp('@/login$@', $response->headers->get('location'));
    }

    /**
     * Simulate default user auth with different roles
     *
     * @param UserInterface $user
     * @param array|null $roles
     * @throws \Exception
     */
    protected function logIn(UserInterface $user = null, array $roles = null)
    {
        if ($user) {
            $roles = $roles ?: $user->getRoles();
        }
        $roles = $roles ?: ['ROLE_ADMIN', 'ROLE_PARTNER', 'ROLE_CONTACTBOOK_MODERATOR', 'ROLE_CONTACTBOOK_ADMIN'];
        $user = $user ?: $this->getUser($roles);
        $container = $this->getContainer();

        $firewallName = $container->getParameter('fos_user.firewall_name');
        $token = new UsernamePasswordToken($user, null, 'main', $roles);

        $session = $container->get('session');
        $session->set('_security_' . $firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @return FactoryMuffin
     */
    protected function getFactory(): FactoryMuffin
    {
        return static::$factory;
    }

    /**
     * @param array $roles
     * @return UserInterface|null
     */
    protected function getUser(array $roles = ['ROLE_ADMIN', 'ROLE_PARTNER', 'ROLE_CONTACTBOOK_MODERATOR', 'ROLE_CONTACTBOOK_ADMIN']): ?UserInterface
    {
        return $this->getFactory()->create(User::class, compact('roles'));
    }

    /**
     * @param bool $createSchema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    private static function regenerateSchema(bool $createSchema = true): void
    {
        $metadata = self::$entityManager->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $tool = new SchemaTool(self::$entityManager);
            $tool->dropSchema($metadata);
            if ($createSchema) {
                $tool->createSchema($metadata);
            }
        }
    }
}
