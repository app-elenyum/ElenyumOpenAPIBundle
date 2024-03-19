<?php

namespace Elenyum\OpenAPI\Tests\Service\Util;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestController extends AbstractController
{
    public function myAction(): JsonResponse
    {
        return $this->json(['success' => true]);
    }
}