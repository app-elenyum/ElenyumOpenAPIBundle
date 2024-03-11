<?php

namespace Elenyum\OpenAPI\Controller;

use Elenyum\OpenAPI\Exception\RenderInvalidArgumentException;
use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Render\JsonOpenApiRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class IndexController extends AbstractController
{
    public function __construct(
        private JsonOpenApiRenderer $jsonOpenApiRenderer,
        private ApiDocGenerator $apiDocGenerator
    ) {
    }

    /**
     * @param Request $request
     * @param $area
     * @return JsonResponse
     */
    public function __invoke(Request $request, $area = 'default')
    {
        try {
            $spec = $this->apiDocGenerator->generate();

            return JsonResponse::fromJsonString(
                $this->jsonOpenApiRenderer->render($spec)
            );
        } catch (RenderInvalidArgumentException $e) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.', $area));
        }
    }
}