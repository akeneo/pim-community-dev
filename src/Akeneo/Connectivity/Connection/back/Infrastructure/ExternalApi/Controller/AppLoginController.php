<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ExternalApi\Controller;

use Akeneo\Connectivity\Connection\Infrastructure\Service\CreateAppUserWithPermissions;
use Akeneo\Connectivity\Connection\Infrastructure\Service\OAuthScopeValidator;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppLoginController
{
    const PRODUCT_EDIT_SCOPE = 'product:edit';
    use WithCheckClientHash;

    private ?ClientInterface $client = null;
    private ?SessionInterface $session;
    private Form $authorizeForm;
    private AuthorizeFormHandler $authorizeFormHandler;
    private OAuth2 $oAuth2Server;
    private EngineInterface $templating;
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;
    private UrlGeneratorInterface $router;
    private ClientManagerInterface $clientManager;
    private string $templateEngineType;
    private EventDispatcherInterface $eventDispatcher;
    protected OAuthScopeValidator $scopeValidator;
    protected CreateAppUserWithPermissions $createAppUserWithPermissions;

    public function __construct(
        RequestStack $requestStack,
        Form $authorizeForm,
        AuthorizeFormHandler $authorizeFormHandler,
        OAuth2 $oAuth2Server,
        EngineInterface $templating,
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $router,
        ClientManagerInterface $clientManager,
        EventDispatcherInterface $eventDispatcher,
        OAuthScopeValidator $scopeValidator,
        SessionInterface $session = null,
        string $templateEngineType = 'twig'
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->authorizeForm = $authorizeForm;
        $this->authorizeFormHandler = $authorizeFormHandler;
        $this->oAuth2Server = $oAuth2Server;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->clientManager = $clientManager;
        $this->templateEngineType = $templateEngineType;
        $this->eventDispatcher = $eventDispatcher;
        $this->scopeValidator = $scopeValidator;
        $this->oAuth2Server->setVariable(
            OAuth2::CONFIG_SUPPORTED_SCOPES,
            self::PRODUCT_EDIT_SCOPE
        );
    }

    /**
     * Authorize.
     */
    public function authorizeAction(Request $request)
    {
        try {
            $this->checkClientHash();
            $scopes = [];
            if ($request->get('scope')) {
                // get scopes for OAuth Apps
                $scopes = explode(',', $request->get('scope'));
                //$this->scopeValidator->validate($scopes);
            }

            $user = $this->tokenStorage->getToken()->getUser();

            if (!$user instanceof UserInterface) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }

            if ($this->session && true === $this->session->get('_fos_oauth_server.ensure_logout')) {
                $this->session->invalidate(600);
                $this->session->set('_fos_oauth_server.ensure_logout', true);
            }

            $form = $this->authorizeForm;
            $formHandler = $this->authorizeFormHandler;

            $event = $this->eventDispatcher->dispatch(
                OAuthEvent::PRE_AUTHORIZATION_PROCESS,
                new OAuthEvent($user, $this->getClient())
            );

            if ($event->isAuthorizedClient()) {
                $scope = $request->get('scope', null);

                return $this->oAuth2Server->finishClientAuthorization(true, $user, $request, $scope);
            }

            if (true === $formHandler->process()) {
                return $this->processSuccess($user, $formHandler, $request);
            }

            $client = $this->getClient();

            $response = $this->templating->renderResponse(
                'AkeneoConnectivityConnectionBundle::authorize_custom.html.'.$this->templateEngineType,
                array(
                    'form'   => $form->createView(),
                    'client' => $client,
                    'scopes' => $scopes
                )
            );

            // create a connection on post authorization process
            return $response;
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /**
     * @param UserInterface        $user
     * @param AuthorizeFormHandler $formHandler
     * @param Request              $request
     *
     * @return Response
     */
    protected function processSuccess(UserInterface $user, AuthorizeFormHandler $formHandler, Request $request)
    {
        if ($this->session && true === $this->session->get('_fos_oauth_server.ensure_logout')) {
            $this->tokenStorage->setToken(null);
            $this->session->invalidate();
        }

        $this->eventDispatcher->dispatch(
            OAuthEvent::POST_AUTHORIZATION_PROCESS,
            new OAuthEvent($user, $this->getClient(), $formHandler->isAccepted())
        );

        $formName = $this->authorizeForm->getName();
        if (!$request->query->all() && $request->request->has($formName)) {
            $request->query->add($request->request->get($formName));
        }

        try {
            return $this->oAuth2Server
                ->finishClientAuthorization($formHandler->isAccepted(), $user, $request, $formHandler->getScope());
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    /**
     * Generate the redirection url when the authorize is completed.
     *
     * @param UserInterface $user
     *
     * @return string
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->router->generate('fos_oauth_server_profile_show');
    }

    /**
     * @return ClientInterface.
     */
    protected function getClient()
    {
        if (null !== $this->client) {
            return $this->client;
        }

        if (null === $request = $this->getCurrentRequest()) {
            throw new NotFoundHttpException('Client not found.');
        }

        if (null === $clientId = $request->get('client_id')) {
            $formData = $request->get($this->authorizeForm->getName(), []);
            $clientId = isset($formData['client_id']) ? $formData['client_id'] : null;
        }

        $this->client = $this->clientManager->findClientByPublicId($clientId);

        if (null === $this->client) {
            throw new NotFoundHttpException('Client not found.');
        }

        return $this->client;
    }

    /**
     * @return null|Request
     */
    private function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('No current request.');
        }

        return $request;
    }
}
