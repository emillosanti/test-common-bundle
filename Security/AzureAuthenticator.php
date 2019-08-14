<?php

namespace SAM\CommonBundle\Security;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AzureAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $router;
    private $encoder;
    private $session;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, UserPasswordEncoderInterface $encoder, SessionInterface $session)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->encoder = $encoder;
        $this->session = $session;
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_azure_check';
    }

    public function getCredentials(Request $request)
    {
        $token = $this->fetchAccessToken($this->getAzureClient());
        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $azureUser = $this->getAzureClient()
            ->fetchUserFromToken($credentials);
     
        $email = $azureUser->claim('unique_name');
        $user = $this->em->getRepository('user')
            ->findOneBy(['email' => $email]);

        // If we doesn't have a user, create it
        if (!$user) {
            $password = $azureUser->getId().$azureUser->claim('iat').time().uniqid();
            $user = new User();
            $user->setEmail($email);
            $user->setPlainPassword($password);
            $encoded = $this->encoder->encodePassword($user, $password);
            $user->setPassword($encoded);
            $user->setCode(strtoupper(substr($user->getFirstName(), 0, 1) . substr($user->getLastName(), 0, 1) . substr($user->getLastName(), strlen($user->getLastName()) - 1, 1)));
            $user->setEnabled(true);
        }

        $user->setUsername($azureUser->getId());
        $user->setFirstName($azureUser->claim('given_name'));
        $user->setLastName($azureUser->claim('family_name'));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return AzureClient
     */
    private function getAzureClient()
    {
        return $this->clientRegistry
            ->getClient('azure');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($this->session->has('_security.main.target_path')) {
            return new RedirectResponse($this->session->get('_security.main.target_path'));
        } else {
            return new RedirectResponse($this->router->generate('deal_flow_list'));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}